<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class GeneralSettings extends Settings
{
    // ── Brand Identity ────────────────────────────────────────────────────────
    /** Site display name (e.g. "RBeverything") */
    public string $site_name;

    /** Relative path to uploaded logo (null = use default/text logo) */
    public ?string $site_logo;

    /** Relative path to uploaded favicon (null = use default favicon) */
    public ?string $site_favicon;

    // ── Contact ───────────────────────────────────────────────────────────────
    public string $contact_email;

    /** WhatsApp phone number including country code (e.g. +62 812 3456 7890) */
    public string $whatsapp_number;

    // ── Social Links (all nullable — omitted links do not render on frontend) ─
    public ?string $linkedin_link;
    public ?string $instagram_link;
    public ?string $youtube_link;
    public ?string $twitter_link;
    public ?string $github_link;

    // ── Website Controls ──────────────────────────────────────────────────────
    /** Public-facing website URL (e.g. https://rbeverything.com) */
    public string $frontend_url;

    /** When true, a maintenance banner is shown in the client area */
    public bool $maintenance_mode;

    /**
     * Translatable tagline: keys are locale codes ('en','id','ms','ja').
     * Access: $settings->web_tagline[app()->getLocale()] ?? $settings->web_tagline['en']
     */
    public array $web_tagline;

    public static function group(): string
    {
        return 'general';
    }
}