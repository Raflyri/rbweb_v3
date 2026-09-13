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

    /** Manual bank transfer account details */
    public ?string $manual_bank_name;
    public ?string $manual_account_number;
    public ?string $manual_account_holder;

    public static function group(): string
    {
        return 'payment';
    }
}
