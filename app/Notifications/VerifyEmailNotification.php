<?php

namespace App\Notifications;

use App\Settings\MailSettings;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;

/**
 * Branded email verification notification for the Client Area panel.
 *
 * Key behaviours:
 *  - 5-minute link expiry (hardcoded + config/auth.php 'verification.expire' = 5)
 *  - Subject & body pulled from MailSettings (editable by Admin in /rbdashboard)
 *  - Falls back to hardcoded Indonesian strings if MailSettings is unavailable
 *  - Sent synchronously (no ShouldQueue) so delivery is instant regardless of
 *    whether a queue worker is running
 */
class VerifyEmailNotification extends VerifyEmail
{
    /** Minutes until the signed URL expires. Must match config('auth.verification.expire'). */
    private const EXPIRE_MINUTES = 5;

    /**
     * Build the mail representation of the notification.
     */
    public function toMail(mixed $notifiable): MailMessage
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        [$subject, $body, $actionLabel] = $this->resolveTemplate($notifiable);

        // Split the body on newlines so each paragraph becomes a ->line() call,
        // which renders correctly in Laravel's markdown-to-email pipeline.
        $lines = array_filter(
            array_map('trim', explode("\n", $body)),
            fn(string $line): bool => $line !== '',
        );

        $mail = (new MailMessage)
            ->subject($subject)
            ->greeting('Halo, ' . $notifiable->name . '!');

        foreach ($lines as $line) {
            $mail->line($line);
        }

        $mail->action($actionLabel, $verificationUrl);
        $mail->salutation('Salam,' . "\n" . config('app.name') . ' Team');

        return $mail;
    }

    /**
     * Build a 5-minute temporary signed URL pointing to Filament's verify route.
     * Falls back to the standard Laravel signed URL if the Filament route is missing.
     */
    protected function verificationUrl(mixed $notifiable): string
    {
        $routeName = 'filament.client-area.auth.email-verification.verify';

        if (! \Illuminate\Support\Facades\Route::has($routeName)) {
            // Fall back: use parent which generates the standard /email/verify URL
            // but override the expiry to 5 minutes.
            return URL::temporarySignedRoute(
                'verification.verify',
                Carbon::now()->addMinutes(self::EXPIRE_MINUTES),
                [
                    'id'   => $notifiable->getKey(),
                    'hash' => sha1($notifiable->getEmailForVerification()),
                ]
            );
        }

        return URL::temporarySignedRoute(
            $routeName,
            Carbon::now()->addMinutes(self::EXPIRE_MINUTES),
            [
                'id'   => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );
    }

    /**
     * Resolve the email template from MailSettings with {name}/{app} substitution.
     * If the settings row is missing (e.g. migration not yet run), gracefully
     * falls back to the hardcoded branded defaults.
     *
     * @return array{string, string, string}  [subject, body, actionLabel]
     */
    private function resolveTemplate(mixed $notifiable): array
    {
        $replacements = [
            '{name}' => $notifiable->name,
            '{app}'  => config('app.name', 'RBeverything'),
        ];

        try {
            /** @var MailSettings $settings */
            $settings = app(MailSettings::class);

            $subject     = str_replace(array_keys($replacements), array_values($replacements), $settings->verification_subject);
            $body        = str_replace(array_keys($replacements), array_values($replacements), $settings->verification_body);
            $actionLabel = $settings->verification_action_label;

            return [$subject, $body, $actionLabel];
        } catch (\Throwable) {
            // Settings not yet seeded — use hardcoded fallback
            $subject = str_replace(array_keys($replacements), array_values($replacements),
                'Verifikasi Alamat Email Anda — {app}');
            $body = str_replace(array_keys($replacements), array_values($replacements),
                "Halo {name},\n\nTerima kasih telah mendaftar di {app}. Klik tombol di bawah ini untuk memverifikasi alamat email Anda.\n\nTautan ini akan kedaluwarsa dalam 5 menit.");

            return [$subject, $body, 'Verifikasi Email Sekarang'];
        }
    }
}
