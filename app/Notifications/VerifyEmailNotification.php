<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

/**
 * Branded email verification notification for the Client Area panel.
 *
 * Extends Laravel's built-in VerifyEmail so all the signature logic is reused,
 * but overrides the mail content to use the application's branding and sends
 * the user directly to the correct Filament verification route.
 *
 * Sent synchronously (ShouldQueue is NOT implemented) to ensure delivery
 * even when no queue worker is running locally or in early-stage deployments.
 * Switch to: implements ShouldQueue once your queue worker is stable in production.
 */
class VerifyEmailNotification extends VerifyEmail
{
    /**
     * Build the mail representation of the notification.
     */
    public function toMail(mixed $notifiable): MailMessage
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject(__('Verifikasi Alamat Email Anda — :app', ['app' => config('app.name')]))
            ->greeting(__('Halo, :name!', ['name' => $notifiable->name]))
            ->line(__('Terima kasih telah mendaftar di :app. Klik tombol di bawah ini untuk memverifikasi alamat email Anda.', ['app' => config('app.name')]))
            ->action(__('Verifikasi Email Sekarang'), $verificationUrl)
            ->line(__('Tautan verifikasi ini akan kedaluwarsa dalam :minutes menit.', [
                'minutes' => config('auth.verification.expire', 60),
            ]))
            ->line(__('Jika Anda tidak membuat akun ini, abaikan email ini.'))
            ->salutation(__('Salam,') . "\n" . config('app.name') . ' Team');
    }

    /**
     * Build the verification URL pointing to the Client Area panel's verify route.
     * The default Laravel URL points to /email/verify — this overrides it to use
     * Filament's signed route: /client-area/email-verification/verify/{id}/{hash}
     */
    protected function verificationUrl(mixed $notifiable): string
    {
        // Attempt to use Filament's named route if it exists.
        // Falls back to the standard Laravel signed URL if not registered.
        $routeName = 'filament.client-area.auth.email-verification.verify';

        if (! \Illuminate\Support\Facades\Route::has($routeName)) {
            return parent::verificationUrl($notifiable);
        }

        return URL::temporarySignedRoute(
            $routeName,
            Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
            [
                'id'   => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );
    }
}
