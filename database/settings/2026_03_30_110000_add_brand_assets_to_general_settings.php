<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

/**
 * Adds brand assets (site_name, site_logo, site_favicon) and new social links
 * (youtube_link, twitter_link, github_link) to the general settings group.
 *
 * Also converts linkedin_link and instagram_link to nullable by updating their
 * values: empty strings become null so the frontend can safely skip them.
 */
return new class extends SettingsMigration
{
    public function up(): void
    {
        // ── Brand Identity ────────────────────────────────────────────────
        $this->migrator->add('general.site_name', 'RBeverything');
        $this->migrator->add('general.site_logo', null);
        $this->migrator->add('general.site_favicon', null);

        // ── New Social Links ──────────────────────────────────────────────
        $this->migrator->add('general.youtube_link', null);
        $this->migrator->add('general.twitter_link', null);
        $this->migrator->add('general.github_link', null);

        // ── Make existing social links nullable (empty string → null) ─────
        // Values already in DB: if they're empty strings, set to null.
        $this->migrator->update('general.linkedin_link', function (?string $current) {
            return filled($current) ? $current : null;
        });
        $this->migrator->update('general.instagram_link', function (?string $current) {
            return filled($current) ? $current : null;
        });
    }

    public function down(): void
    {
        $this->migrator->delete('general.site_name');
        $this->migrator->delete('general.site_logo');
        $this->migrator->delete('general.site_favicon');
        $this->migrator->delete('general.youtube_link');
        $this->migrator->delete('general.twitter_link');
        $this->migrator->delete('general.github_link');
    }
};
