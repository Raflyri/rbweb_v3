<?php

namespace App\Notifications\Auth;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LoginSuccess extends Notification
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
            ->subject('Pemberitahuan Login Sukses - ' . config('app.name'))
            ->greeting("Halo, {$notifiable->name}!")
            ->line('Kami mendeteksi login baru pada akun Anda.')
            ->line('Waktu: ' . $this->time->format('d/m/Y H:i:s'))
            ->line('Alamat IP: ' . $this->ipAddress)
            ->line('Jika ini adalah Anda, Anda bisa mengabaikan email ini.')
            ->line('Jika Anda tidak merasa melakukan login, segera ganti password Anda dan amankan akun Anda.')
            ->action('Ke Halaman Akun', url('/'))
            ->salutation("Salam,\n" . config('app.name'));
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
