<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

/**
 * Payment gateway settings stored in the `settings` table (group = 'payment').
 *
 * Provides control over Midtrans Core API and Manual Bank Transfer configurations,
 * editable directly from the Filament Admin Panel (/rbdashboard).
 */
class PaymentSettings extends Settings
{
    /** Master switch for Midtrans payment gateway */
    public bool $midtrans_is_active;

    /** Environment mode: false for Sandbox, true for Production */
    public bool $midtrans_is_production;

    /** Midtrans Server Key */
    public ?string $midtrans_server_key;

    /** Midtrans Client Key */
    public ?string $midtrans_client_key;

    /** Currently active default gateway: 'midtrans' or 'manual_transfer' */
    public string $active_gateway;

    /**
     * Array of enabled Core API channels.
     * e.g. ['qris', 'bca_va', 'bni_va', 'bri_va', 'mandiri_bill', 'permata_va']
     *
     * @var array<string>
     */
    public array $midtrans_enabled_channels;

    /** Master switch for Paylabs payment gateway */
    public bool $paylabs_is_active;

    /** Environment mode: false for Sandbox (sit-pay), true for Production (pay) */
    public bool $paylabs_is_production;

    /** Paylabs Partner / Merchant ID */
    public ?string $paylabs_merchant_id;

    /** Paylabs Store ID (opsional) */
    public ?string $paylabs_store_id;

    /** Paylabs RSA Private Key (PKCS#8) */
    public ?string $paylabs_private_key;

    /** Paylabs Public Key for webhook signature verification */
    public ?string $paylabs_public_key;

    /**
     * Array of enabled Paylabs channels.
     * e.g. ['paylabs_qris', 'paylabs_bca_va', 'paylabs_bni_va', 'paylabs_bri_va', 'paylabs_mandiri_va', 'paylabs_permata_va']
     *
     * @var array<string>
     */
    public array $paylabs_enabled_channels;

    /** Manual bank transfer account details */
    public ?string $manual_bank_name;
    public ?string $manual_account_number;
    public ?string $manual_account_holder;

    public static function group(): string
    {
        return 'payment';
    }
}
