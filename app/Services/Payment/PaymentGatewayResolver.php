<?php

namespace App\Services\Payment;

use App\Contracts\PaymentGateway;

/**
 * Hands out the payment gateway that is currently switched on.
 *
 * This is the single place that knows which methods exist. Phase 5 adds one
 * `case 'midtrans'` here — behind its own is_active guard — and nothing that
 * calls resolve() has to change.
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
            default => new ManualTransferGateway(),
        };
    }

    public function activeGatewayKey(): string
    {
        return (string) config('services.payment.active_gateway', ManualTransferGateway::KEY);
    }
}
