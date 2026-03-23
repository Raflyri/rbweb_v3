<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class GeneralSettings extends Settings
{
    public string $whatsapp_number;
    public string $contact_email;
    public string $linkedin_link;
    public string $instagram_link;

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