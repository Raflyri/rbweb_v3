<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderStatusUpdated extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        protected \App\Models\Order $order
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $order = $this->order;
        $status = $order->status;
        
        $mail = (new MailMessage)
            ->greeting("Halo, {$order->customer_name}!");

        if ($status === \App\Support\OrderStatus::DIPROSES) {
            $mail->subject("Pesanan Anda Sedang Diproses: {$order->order_number}")
                 ->line("Pesanan Anda dengan nomor {$order->order_number} saat ini sedang kami proses.")
                 ->line("Kami sedang menyiapkan produk Anda.");
        } elseif ($status === \App\Support\OrderStatus::DIKIRIM) {
            $mail->subject("Pesanan Anda Telah Dikirim: {$order->order_number}")
                 ->line("Kabar baik! Pesanan Anda dengan nomor {$order->order_number} telah dikirim.")
                 ->line("Silakan pantau status pengiriman pesanan Anda secara berkala.");
        } elseif ($status === \App\Support\OrderStatus::SELESAI) {
            $mail->subject("Pesanan Anda Telah Selesai: {$order->order_number}")
                 ->line("Pesanan Anda dengan nomor {$order->order_number} telah berstatus selesai/diterima.")
                 ->line("Terima kasih telah berbelanja bersama kami! Semoga Anda puas dengan layanan kami.");
        } else {
            $mail->subject("Pembaruan Status Pesanan: {$order->order_number}")
                 ->line("Status pesanan Anda dengan nomor {$order->order_number} telah diperbarui menjadi: " . \App\Support\OrderStatus::label($status));
        }

        return $mail
            ->action('Lihat Detail Pesanan', route('order.pending', $order->public_token))
            ->salutation("Salam hangat,\n" . config('app.name'));
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
