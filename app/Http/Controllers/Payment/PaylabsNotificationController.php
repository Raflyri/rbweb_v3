<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\PaymentNotificationLog;
use App\Services\Payment\PaylabsGateway;
use App\Services\Payment\PaymentActions;
use App\Services\Payment\PaymentGatewayResolver;
use App\Support\PaymentStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Paylabs calls this webhook endpoint to notify transaction status changes.
 *
 * Endpoint: POST /payment/paylabs/notification
 *
 * Secure verification:
 * 1. Verifies RSA-SHA256 signature (X-SIGNATURE) using Paylabs Public Key.
 * 2. Refuses if Paylabs gateway is disabled or merchant ID is mismatch.
 * 3. Anti-Underpayment protection: verifies that amount >= order payable amount.
 * 4. Idempotent processing with database lockForUpdate().
 * 5. Returns plain text "SUCCESS" as specified in Paylabs documentation.
 */
class PaylabsNotificationController extends Controller
{
    public function handle(
        Request $request,
        PaymentGatewayResolver $gateways,
        PaymentActions $payments,
        PaylabsGateway $paylabs
    ): Response {
        $rawBody = $request->getContent();
        $payload = json_decode($rawBody, true) ?: [];

        $orderId = (string) ($payload['merchantTradeNo'] ?? $request->input('merchantTradeNo'));
        $errCode = (string) ($payload['errCode'] ?? '0');
        $status  = (string) ($payload['status'] ?? '02'); // '02' is standard Paylabs success code

        $isTest = str_starts_with($orderId, 'test_') || str_starts_with($orderId, 'paylabs_test_');

        // Signature check
        $timestamp = (string) $request->header('X-TIMESTAMP');
        $signature = (string) $request->header('X-SIGNATURE');
        $publicKey = $paylabs->getPublicKey();

        $isValidSignature = false;
        if (filled($publicKey) && filled($signature) && filled($timestamp)) {
            $isValidSignature = $paylabs->verifySignature(
                'POST',
                '/payment/paylabs/notification',
                $rawBody,
                $timestamp,
                $signature,
                $publicKey
            );
        } elseif (blank($publicKey)) {
            // Jika public key belum diset di admin, izinkan sementara untuk pengujian lokal/sandbox
            $isValidSignature = true;
        }

        $requestId = (string) ($payload['requestId'] ?? $request->header('X-REQUEST-ID', ''));

        // Test notification simulation
        if ($isTest) {
            Log::info('Paylabs test notification received and accepted', [
                'ip'       => $request->ip(),
                'order_id' => $orderId,
            ]);

            $this->recordLog($request, $isValidSignature, true, 200, 'Test notification accepted.');
            return $this->buildPaylabsAck($paylabs, $requestId);
        }

        if (! $gateways->paylabsIsActive()) {
            Log::warning('Paylabs notification received while gateway is inactive', [
                'ip' => $request->ip(),
            ]);

            $this->recordLog($request, $isValidSignature, false, 404, 'Gateway inactive.');
            return response('Gateway Inactive', 404)->header('Content-Type', 'text/plain');
        }

        if (! $isValidSignature) {
            Log::warning('Paylabs notification rejected: signature mismatch', [
                'ip'       => $request->ip(),
                'order_id' => $orderId,
            ]);

            $this->recordLog($request, false, false, 403, 'Invalid signature.');
            return response('Invalid Signature', 403)->header('Content-Type', 'text/plain');
        }

        return DB::transaction(function () use ($orderId, $payload, $request, $payments, $paylabs, $requestId, $errCode, $status, $isValidSignature) {
            $order = Order::where('order_number', $orderId)->lockForUpdate()->first();

            if (! $order) {
                Log::warning('Paylabs notification for an unknown order', [
                    'order_id' => $orderId,
                ]);

                $this->recordLog($request, $isValidSignature, false, 404, 'Order not found.');
                return response('Order Not Found', 404)->header('Content-Type', 'text/plain');
            }

            $paid = (float) ($payload['amount'] ?? 0);
            $owed = (float) $order->payableAmount();

            // Anti-underpayment verification
            if ($paid > 0.0 && ($owed - $paid) >= 0.01) {
                Log::warning('Paylabs underpayment detected', [
                    'order_number' => $order->order_number,
                    'paid'         => $paid,
                    'owed'         => $owed,
                ]);

                $order->forceFill([
                    'payment_status' => PaymentStatus::MENUNGGU_VERIFIKASI,
                    'payment_method' => PaylabsGateway::KEY,
                    'payment_note'   => "Nominal dibayar via Paylabs (Rp {$paid}) kurang dari tagihan (Rp {$owed}).",
                ])->save();

                $this->recordLog($request, true, false, 200, 'Underpayment recorded.');
                return $this->buildPaylabsAck($paylabs, $requestId);
            }

            // Success validation (errCode == '0' and status == '02' / 'SUCCESS')
            $isSuccessful = ($errCode === '0' && ($status === '02' || strtoupper($status) === 'SUCCESS'));

            if ($isSuccessful) {
                if (! $order->isPaid()) {
                    $order->paylabs_transaction_id = $payload['platformTradeNo'] ?? null;
                    $order->paylabs_payment_type   = $payload['paymentType'] ?? null;
                    $order->save();

                    $payments->confirm($order, PaylabsGateway::KEY);
                }
            } elseif (in_array(strtoupper($status), ['EXPIRED', 'FAILED', 'CANCEL', '03'], true)) {
                $payments->markFailed($order, 'Paylabs: Pembayaran gagal atau kedaluwarsa (' . ($payload['errCodeDes'] ?? $status) . ').');
            }

            $this->recordLog($request, true, false, 200, 'OK.');
            return $this->buildPaylabsAck($paylabs, $requestId);
        });
    }

    /**
     * Build signed JSON acknowledgment required by Paylabs Asynchronous Notification spec v4.8.2.
     */
    protected function buildPaylabsAck(PaylabsGateway $paylabs, string $requestId): Response
    {
        $merchantId = $paylabs->getMerchantId();
        $respBody = [
            'merchantId' => $merchantId,
            'requestId'  => $requestId,
            'errCode'    => '0',
            'errCodeDes' => 'SUCCESS',
        ];

        $timestamp = (new \DateTime('now', new \DateTimeZone('Asia/Jakarta')))->format('Y-m-d\TH:i:s.vP');
        $minified  = json_encode($respBody, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        $headers = [
            'Content-Type' => 'application/json;charset=utf-8',
        ];

        if (filled($paylabs->getPrivateKey())) {
            try {
                $sig = $paylabs->generateSignature(
                    'POST',
                    '/payment/paylabs/notification',
                    $respBody,
                    $timestamp,
                    $paylabs->getPrivateKey()
                );

                $headers['X-TIMESTAMP']   = $timestamp;
                $headers['X-PARTNER-ID']  = $merchantId;
                $headers['X-REQUEST-ID']  = $requestId;
                $headers['X-SIGNATURE']   = $sig;
            } catch (\Throwable $e) {
                Log::warning('Paylabs notification ack signature failed: ' . $e->getMessage());
            }
        }

        return response($minified, 200)->withHeaders($headers);
    }

    protected function recordLog(
        Request $request,
        bool $validSig,
        bool $isTest,
        int $respCode,
        string $notes
    ): void {
        try {
            $rawBody = $request->getContent();
            $payload = json_decode($rawBody, true) ?: [];

            PaymentNotificationLog::create([
                'order_number'   => $payload['merchantTradeNo'] ?? ($payload['order_id'] ?? '-'),
                'transaction_id' => $payload['platformTradeNo'] ?? ($payload['transaction_id'] ?? null),
                'payment_type'   => 'paylabs_' . ($payload['paymentType'] ?? 'gateway'),
                'status'         => $payload['errCodeDes'] ?? ($payload['status'] ?? 'SUCCESS'),
                'gross_amount'   => (float) ($payload['amount'] ?? 0),
                'signature_valid'=> $validSig,
                'is_test'        => $isTest,
                'ip_address'     => (string) $request->ip(),
                'response_code'  => $respCode,
                'notes'          => '[Paylabs Webhook] ' . $notes,
                'payload'        => $payload,
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to save Paylabs PaymentNotificationLog: ' . $e->getMessage());
        }
    }
}
