<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\LaravelSettings\Migrations\SettingsMigration;

/**
 * Seeds the default email verification template into the `settings` table
 * under the 'mail' group. Uses spatie/laravel-settings SettingsMigration
 * so values are correctly serialised and can be managed via the Admin UI.
 */
return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add(
            'mail.verification_subject',
            'Verifikasi Alamat Email Anda — {app}',
        );

        $this->migrator->add(
            'mail.verification_body',
            "Halo {name},\n\nTerima kasih telah mendaftar di {app}. Klik tombol di bawah ini untuk memverifikasi alamat email Anda.\n\nTautan verifikasi ini akan kedaluwarsa dalam 5 menit. Pastikan Anda mengkliknya sebelum waktu habis.\n\nJika Anda tidak membuat akun ini, abaikan email ini.",
        );

        $this->migrator->add(
            'mail.verification_action_label',
            'Verifikasi Email Sekarang',
        );
    }

    public function down(): void
    {
        $this->migrator->delete('mail.verification_subject');
        $this->migrator->delete('mail.verification_body');
        $this->migrator->delete('mail.verification_action_label');
    }
};
