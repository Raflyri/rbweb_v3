<?php

namespace App\Notifications\Auth;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LoginFailedWarning extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public readonly \DateTimeInterface $time,
        public readonly string $ipAddress
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Peringatan: Percobaan Login Gagal - ' . config('app.name'))
            ->greeting('Peringatan Keamanan!')
            ->line('Kami mendeteksi adanya 3 kali percobaan login yang gagal ke akun Anda.')
            ->line('Waktu: ' . $this->time->format('d/m/Y H:i:s'))
            ->line('Alamat IP: ' . $this->ipAddress)
            ->line('Jika Anda yang mencoba masuk dan lupa kata sandi, silakan gunakan fitur Lupa Kata Sandi.')
            ->line('Jika ini bukan Anda, akun Anda mungkin sedang menjadi target percobaan akses tidak sah.')
            ->action('Reset Password', url('/forgot-password'))
            ->salutation("Salam,\nTim Keamanan " . config('app.name'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
