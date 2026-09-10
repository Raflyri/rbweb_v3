<?php

namespace App\Services\Payment;

use App\Contracts\PaymentGateway;

/**
 * Hands out the payment gateway that is currently switched on.
 *
 * This is the single place that knows which methods exist, and the single
 * place that decides whether Midtrans is allowed out.
 *
 * An unrecognised name falls back to manual transfer rather than throwing: a
 * typo in config must not take checkout down, and a working bank transfer is a
 * safe thing to land on.
 */
class PaymentGatewayResolver
{
    public function resolve(): PaymentGateway
    {
        return match ($this->activeGatewayKey()) {
            MidtransGateway::KEY => $this->midtransOrFallback(),
            default              => new ManualTransferGateway(),
        };
    }

    public function activeGatewayKey(): string
    {
        return (string) config('services.payment.active_gateway', ManualTransferGateway::KEY);
    }

    /**
     * The one gate Midtrans has to pass, checked in exactly one place.
     *
     * Selecting 'midtrans' as the active gateway is not enough on its own —
     * MIDTRANS_IS_ACTIVE has to be true as well. That is deliberate: the two
     * settings live in different files, so nobody flips this on by editing
     * config in passing, and a half-finished setup keeps taking bank transfers
     * instead of failing at checkout.
     *
     * A blank server key counts as inactive too. It also closes the webhook's
     * worst case: signature verification hashes with the server key, and with
     * an empty key anyone could compute a "valid" signature for themselves.
     */
    public function midtransIsActive(): bool
    {
        return (bool) config('services.midtrans.is_active', false)
            && filled(config('services.midtrans.server_key'));
    }

    protected function midtransOrFallback(): PaymentGateway
    {
        return $this->midtransIsActive()
            ? new MidtransGateway()
            : new ManualTransferGateway();
    }
}
