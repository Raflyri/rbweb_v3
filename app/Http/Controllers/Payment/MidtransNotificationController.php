<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\Payment\MidtransGateway;
use App\Services\Payment\PaymentActions;
use App\Services\Payment\PaymentGatewayResolver;
use App\Support\PaymentStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Midtrans calls this to say what happened to a payment.
 *
 * Two things make it safe to expose. First, it refuses everything while the
 * gateway is switched off — including, crucially, while the server key is
 * blank: the signature is a hash that ends with that key, so an empty one
 * would let anyone compute a "valid" signature and mark their own order paid.
 * Second, nothing is read out of the request body until the signature checks
 * out against our own copy of the key.
 *
 * It always answers 200 once it has decided to act, whatever it decided:
 * Midtrans retries anything else, and a retry loop over an order we have
 * already handled helps nobody.
 */
class MidtransNotificationController extends Controller
{
    public function handle(
        Request $request,
        PaymentGatewayResolver $gateways,
        PaymentActions $payments,
    ): JsonResponse {
        if (! $gateways->midtransIsActive()) {
            Log::warning('Midtrans notification received while the gateway is inactive', [
                'ip' => $request->ip(),
            ]);

            // 404, not 403: while Midtrans is off this endpoint effectively
            // does not exist, and saying so tells a prober nothing.
            return response()->json(['message' => 'Not found.'], 404);
        }

        if (! $this->signatureIsValid($request)) {
            Log::warning('Midtrans notification rejected: signature mismatch', [
                'ip'       => $request->ip(),
                'order_id' => $request->input('order_id'),
            ]);

            return response()->json(['message' => 'Invalid signature.'], 403);
        }

        $order = Order::where('order_number', $request->input('order_id'))->first();

        if (! $order) {
            Log::warning('Midtrans notification for an unknown order', [
                'order_id' => $request->input('order_id'),
            ]);

            return response()->json(['message' => 'Order not found.'], 404);
        }

        $this->apply($order, $request, $payments);

        return response()->json(['message' => 'OK.']);
    }

    /**
     * sha512(order_id + status_code + gross_amount + server_key), per Midtrans'
     * documentation, compared in constant time.
     */
    protected function signatureIsValid(Request $request): bool
    {
        $provided = (string) $request->input('signature_key');

        if ($provided === '') {
            return false;
        }

        $expected = hash('sha512',
            (string) $request->input('order_id')
            . (string) $request->input('status_code')
            . (string) $request->input('gross_amount')
            . (string) config('services.midtrans.server_key')
        );

        return hash_equals($expected, $provided);
    }

    /**
     * Translate Midtrans' transaction_status into our own payment status.
     *
     * Anything not listed is left alone on purpose: an unfamiliar status is a
     * reason to look, not to guess at what it means for someone's money.
     */
    protected function apply(Order $order, Request $request, PaymentActions $payments): void
    {
        $status = (string) $request->input('transaction_status');
        $fraud  = (string) $request->input('fraud_status');

        $this->warnOnAmountMismatch($order, $request);

        switch ($status) {
            case 'capture':
                // Card payments land here first. 'challenge' means Midtrans
                // wants a human to look before the money is treated as real.
                if ($fraud === 'challenge') {
                    $order->forceFill([
                        'payment_status' => PaymentStatus::MENUNGGU_VERIFIKASI,
                        'payment_method' => MidtransGateway::KEY,
                        'payment_note'   => 'Midtrans menandai transaksi ini untuk ditinjau (fraud_status: challenge).',
                    ])->save();

                    return;
                }

                if ($fraud === 'deny') {
                    $payments->markFailed($order, 'Transaksi ditolak Midtrans (fraud_status: deny).');

                    return;
                }

                $this->confirmOnce($order, $payments);

                return;

            case 'settlement':
                $this->confirmOnce($order, $payments);

                return;

            case 'pending':
                // Nothing to do: the order is already waiting for payment, and
                // overwriting the status would lose a rejection note.
                return;

            case 'deny':
            case 'cancel':
            case 'expire':
                $payments->markFailed($order, 'Pembayaran Midtrans ' . $status . '.');

                return;

            case 'refund':
            case 'partial_refund':
                $order->forceFill([
                    'payment_status' => PaymentStatus::DIBATALKAN,
                    'payment_note'   => 'Pembayaran dikembalikan (Midtrans: ' . $status . ').',
                ])->save();

                return;

            default:
                Log::info('Midtrans notification with an unhandled transaction_status', [
                    'order_number'       => $order->order_number,
                    'transaction_status' => $status,
                ]);
        }
    }

    /**
     * Midtrans retries a notification until it gets a 200, so the same
     * settlement can arrive several times. Confirming twice would send the
     * buyer a second "payment received" email for one payment.
     */
    protected function confirmOnce(Order $order, PaymentActions $payments): void
    {
        if ($order->isPaid()) {
            return;
        }

        $payments->confirm($order, MidtransGateway::KEY);
    }

    /**
     * The signature covers gross_amount, so a mismatch is not tampering — it
     * means what was charged differs from what we think is owed. Worth a log
     * line for whoever reconciles the books.
     */
    protected function warnOnAmountMismatch(Order $order, Request $request): void
    {
        $paid  = (float) $request->input('gross_amount');
        $owed  = (float) $order->payableAmount();

        if ($paid > 0.0 && abs($paid - $owed) >= 0.01) {
            Log::warning('Midtrans amount differs from the order total', [
                'order_number' => $order->order_number,
                'paid'         => $paid,
                'owed'         => $owed,
            ]);
        }
    }
}
