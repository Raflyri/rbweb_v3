<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

/**
 * Mail template settings stored in the `settings` table (group = 'mail').
 *
 * Properties support the following placeholders that are resolved at send-time:
 *   {name}  → recipient's display name
 *   {app}   → config('app.name')
 *
 * Default values are seeded by the create_settings_for_mail migration.
 */
class MailSettings extends Settings
{
    /** Email subject line. Supports {name} and {app} placeholders. */
    public string $verification_subject;

    /**
     * Email body text (plain / markdown — no raw HTML).
     * Supports {name} and {app} placeholders.
     * Each \n becomes a new paragraph line in Laravel's MailMessage.
     */
    public string $verification_body;

    /** Label for the verification call-to-action button. */
    public string $verification_action_label;

    public static function group(): string
    {
        return 'mail';
    }
}
