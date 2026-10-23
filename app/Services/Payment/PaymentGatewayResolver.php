<?php

namespace App\Services\Payment;

use App\Contracts\PaymentGateway;
use App\Settings\PaymentSettings;

/**
 * Hands out the payment gateway that is currently switched on.
 *
 * Reads from App\Settings\PaymentSettings (editable via /rbdashboard) with safe fallback
 * to config/services.php. Supports Single Gateway (Midtrans or Paylabs), Dual Gateways ('both'),
 * and Manual Transfer fallback.
 */
class PaymentGatewayResolver
{
    public function resolve(): PaymentGateway
    {
        return match ($this->activeGatewayKey()) {
            MidtransGateway::KEY => $this->midtransOrFallback(),
            PaylabsGateway::KEY  => $this->paylabsOrFallback(),
            'both'               => $this->bothOrFallback(),
            default              => new ManualTransferGateway(),
        };
    }

    public function resolveForOrder(?\App\Models\Order $order = null): PaymentGateway
    {
        if ($order) {
            if ($order->payment_method === PaylabsGateway::KEY || filled($order->paylabs_payment_type)) {
                return $this->paylabsOrFallback();
            }

            if ($order->payment_method === MidtransGateway::KEY || filled($order->midtrans_payment_type)) {
                return $this->midtransOrFallback();
            }

            if ($order->payment_method === ManualTransferGateway::KEY) {
                return new ManualTransferGateway();
            }
        }

        return $this->resolve();
    }

    public function activeGatewayKey(): string
    {
        if (app()->runningUnitTests()) {
            return (string) config('services.payment.active_gateway', ManualTransferGateway::KEY);
        }

        try {
            $settings = app(PaymentSettings::class);
            return $settings->active_gateway ?: (string) config('services.payment.active_gateway', ManualTransferGateway::KEY);
        } catch (\Throwable) {
            return (string) config('services.payment.active_gateway', ManualTransferGateway::KEY);
        }
    }

    public function midtransIsActive(): bool
    {
        $activeKey = $this->activeGatewayKey();
        if (! in_array($activeKey, [MidtransGateway::KEY, 'both'], true) && ! app()->runningUnitTests()) {
            return false;
        }

        $serverKey = $this->serverKey();
        if (blank($serverKey)) {
            return false;
        }

        if (config('services.midtrans.is_active') === true) {
            return true;
        }

        try {
            $settings = app(PaymentSettings::class);
            if ($settings->midtrans_is_active && ! app()->runningUnitTests()) {
                return true;
            }
        } catch (\Throwable) {
        }

        return (bool) config('services.midtrans.is_active', false);
    }

    public function paylabsIsActive(): bool
    {
        $activeKey = $this->activeGatewayKey();
        if (! in_array($activeKey, [PaylabsGateway::KEY, 'both'], true) && ! app()->runningUnitTests()) {
            return false;
        }

        $merchantId = $this->paylabsMerchantId();
        if (blank($merchantId)) {
            return false;
        }

        if (config('services.paylabs.is_active') === true) {
            return true;
        }

        try {
            $settings = app(PaymentSettings::class);
            if ($settings->paylabs_is_active && ! app()->runningUnitTests()) {
                return true;
            }
        } catch (\Throwable) {
        }

        return (bool) config('services.paylabs.is_active', false);
    }

    public function serverKey(): string
    {
        $fromConfig = (string) config('services.midtrans.server_key', '');
        if (filled($fromConfig)) {
            return $fromConfig;
        }

        try {
            $settings = app(PaymentSettings::class);
            return (string) ($settings->midtrans_server_key ?? '');
        } catch (\Throwable) {
            return '';
        }
    }

    public function paylabsMerchantId(): string
    {
        $fromConfig = (string) config('services.paylabs.merchant_id', '');
        if (filled($fromConfig)) {
            return $fromConfig;
        }

        try {
            $settings = app(PaymentSettings::class);
            return (string) ($settings->paylabs_merchant_id ?? '');
        } catch (\Throwable) {
            return '';
        }
    }

    public function midtransOrFallback(): PaymentGateway
    {
        return $this->midtransIsActive()
            ? new MidtransGateway()
            : new ManualTransferGateway();
    }

    public function paylabsOrFallback(): PaymentGateway
    {
        return $this->paylabsIsActive()
            ? new PaylabsGateway()
            : new ManualTransferGateway();
    }

    public function bothOrFallback(): PaymentGateway
    {
        if ($this->midtransIsActive()) {
            return new MidtransGateway();
        }

        if ($this->paylabsIsActive()) {
            return new PaylabsGateway();
        }

        return new ManualTransferGateway();
    }
}
