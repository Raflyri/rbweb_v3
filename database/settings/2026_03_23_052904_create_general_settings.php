<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('general.whatsapp_number', '');
        $this->migrator->add('general.contact_email', '');
        $this->migrator->add('general.linkedin_link', '');
        $this->migrator->add('general.instagram_link', '');
        $this->migrator->add('general.web_tagline', [
            'en' => 'Your Partner in the Digital Age',
            'id' => 'Mitra Anda di Era Digital',
            'ms' => 'Rakan Anda di Era Digital',
            'ja' => 'デジタル時代のパートナー',
        ]);
    }
};
