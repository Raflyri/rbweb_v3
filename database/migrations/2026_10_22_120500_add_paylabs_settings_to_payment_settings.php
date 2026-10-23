<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('payment.paylabs_is_active', (bool) env('PAYLABS_IS_ACTIVE', false));
        $this->migrator->add('payment.paylabs_is_production', (bool) env('PAYLABS_IS_PRODUCTION', false));
        $this->migrator->add('payment.paylabs_merchant_id', (string) env('PAYLABS_MERCHANT_ID', ''));
        $this->migrator->add('payment.paylabs_store_id', (string) env('PAYLABS_STORE_ID', ''));
        $this->migrator->add('payment.paylabs_private_key', (string) env('PAYLABS_PRIVATE_KEY', ''));
        $this->migrator->add('payment.paylabs_public_key', (string) env('PAYLABS_PUBLIC_KEY', ''));
        $this->migrator->add('payment.paylabs_enabled_channels', [
            'paylabs_qris',
            'paylabs_bca_va',
            'paylabs_bni_va',
            'paylabs_bri_va',
            'paylabs_mandiri_va',
            'paylabs_permata_va',
        ]);
    }

    public function down(): void
    {
        $this->migrator->delete('payment.paylabs_is_active');
        $this->migrator->delete('payment.paylabs_is_production');
        $this->migrator->delete('payment.paylabs_merchant_id');
        $this->migrator->delete('payment.paylabs_store_id');
        $this->migrator->delete('payment.paylabs_private_key');
        $this->migrator->delete('payment.paylabs_public_key');
        $this->migrator->delete('payment.paylabs_enabled_channels');
    }
};
