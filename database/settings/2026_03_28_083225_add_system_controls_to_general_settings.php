<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('general.frontend_url', 'https://rbeverything.com');
        $this->migrator->add('general.maintenance_mode', false);
    }

    public function down(): void
    {
        $this->migrator->delete('general.frontend_url');
        $this->migrator->delete('general.maintenance_mode');
    }
};
