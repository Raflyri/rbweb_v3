<?php

namespace App\Services\Payment;

use App\Contracts\PaymentGateway;
use App\Exceptions\PaymentGatewayException;
use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Midtrans\Config as MidtransConfig;
use Midtrans\Snap;

/**
 * Midtrans Snap — written, tested, and deliberately not switched on.
 *
 * Nothing here runs until MIDTRANS_IS_ACTIVE is true in the environment;
 * PaymentGatewayResolver will not hand this class out otherwise, whatever
 * services.payment.active_gateway says. The point of writing it now is that
 * turning it on later is filling in three env values, not a coding project.
 */
class MidtransGateway implements PaymentGateway
{
    public const KEY = 'midtrans';

    public function key(): string
    {
        return self::KEY;
    }

    public function name(): string
    {
        return 'Midtrans';
    }

    public function isConfigured(): bool
    {
        return filled(config('services.midtrans.server_key'));
    }

    /**
     * Ask Midtrans for a Snap transaction and hand the view somewhere to send
     * the buyer.
     *
     * Every failure — bad key, network timeout, malformed response — comes back
     * as PaymentGatewayException. OrderController catches that and shows a
     * sentence the buyer can act on; the provider's own error text goes to the
     * log, not to the page.
     *
     * @return array<string, mixed>
     */
    public function charge(Order $order): array
    {
        if (! $this->isConfigured()) {
            throw new PaymentGatewayException('Midtrans server key is not set.', self::KEY);
        }

        $this->applyConfig();

        $amount = $order->payableAmount();

        try {
            $snap = Snap::createTransaction($this->transactionParams($order, $amount));
        } catch (\Throwable $e) {
            // The buyer never sees this; PaymentGatewayException::userMessage()
            // is what reaches the page.
            Log::error('Midtrans Snap transaction failed', [
                'order_number' => $order->order_number,
                'error'        => $e->getMessage(),
            ]);

            throw new PaymentGatewayException(
                'Midtrans rejected the transaction request: ' . $e->getMessage(),
                self::KEY,
                $e,
            );
        }

        $redirectUrl = $snap->redirect_url ?? null;

        if (blank($redirectUrl)) {
            throw new PaymentGatewayException('Midtrans returned no redirect URL.', self::KEY);
        }

        return [
            'type'             => 'redirect',
            'gateway'          => self::KEY,
            'name'             => $this->name(),
            'configured'       => true,
            'amount'           => $amount,
            'formatted_amount' => Order::formatRupiah($amount),
            'reference'        => $order->order_number,
            'snap_token'       => $snap->token ?? null,
            'url'              => $redirectUrl,
            'instructions'     => [
                'Klik tombol pembayaran untuk memilih metode dan menyelesaikan transaksi.',
                'Status pesanan diperbarui otomatis setelah pembayaran berhasil.',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function transactionParams(Order $order, float $amount): array
    {
        $params = [
            'transaction_details' => [
                // The same reference the buyer and the shop already use.
                'order_id' => $order->order_number,
                // Rupiah has no cents at Midtrans: gross_amount must be an integer.
                'gross_amount' => (int) round($amount),
            ],
            'customer_details' => [
                'first_name' => $order->customer_name,
                'email'      => $order->customer_email,
                'phone'      => $order->customer_phone,
            ],
        ];

        if ($order->needsShipping() && filled($order->shipping_address)) {
            $params['customer_details']['shipping_address'] = [
                'first_name' => $order->customer_name,
                'phone'      => $order->customer_phone,
                'address'    => $order->shipping_address,
                'country_code' => 'IDN',
            ];
        }

        return $params;
    }

    /**
     * The SDK keeps its configuration in static properties, so it has to be
     * set before every call rather than once at boot — a queue worker or a
     * second request in the same process would otherwise inherit whatever the
     * last caller left behind.
     */
    protected function applyConfig(): void
    {
        MidtransConfig::$serverKey    = (string) config('services.midtrans.server_key');
        MidtransConfig::$clientKey    = (string) config('services.midtrans.client_key');
        MidtransConfig::$isProduction = (bool) config('services.midtrans.is_production', false);
        MidtransConfig::$isSanitized  = true;
        MidtransConfig::$is3ds        = true;
    }
}
