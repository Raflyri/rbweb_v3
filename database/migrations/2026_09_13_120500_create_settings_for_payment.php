<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\LaravelSettings\Migrations\SettingsMigration;

/**
 * Seeds default payment settings into the `settings` table under group 'payment'.
 */
return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('payment.midtrans_is_active', (bool) env('MIDTRANS_IS_ACTIVE', false));
        $this->migrator->add('payment.midtrans_is_production', (bool) env('MIDTRANS_IS_PRODUCTION', false));
        $this->migrator->add('payment.midtrans_server_key', env('MIDTRANS_SERVER_KEY', ''));
        $this->migrator->add('payment.midtrans_client_key', env('MIDTRANS_CLIENT_KEY', ''));
        $this->migrator->add('payment.active_gateway', (string) config('services.payment.active_gateway', 'manual_transfer'));
        $this->migrator->add('payment.midtrans_enabled_channels', [
            'qris',
            'bca_va',
            'bni_va',
            'bri_va',
            'mandiri_bill',
            'permata_va',
        ]);
        $this->migrator->add('payment.manual_bank_name', (string) config('services.manual_transfer.bank_name', ''));
        $this->migrator->add('payment.manual_account_number', (string) config('services.manual_transfer.account_number', ''));
        $this->migrator->add('payment.manual_account_holder', (string) config('services.manual_transfer.account_holder', ''));
    }

    public function down(): void
    {
        $this->migrator->delete('payment.midtrans_is_active');
        $this->migrator->delete('payment.midtrans_is_production');
        $this->migrator->delete('payment.midtrans_server_key');
        $this->migrator->delete('payment.midtrans_client_key');
        $this->migrator->delete('payment.active_gateway');
        $this->migrator->delete('payment.midtrans_enabled_channels');
        $this->migrator->delete('payment.manual_bank_name');
        $this->migrator->delete('payment.manual_account_number');
        $this->migrator->delete('payment.manual_account_holder');
    }
};
