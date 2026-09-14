<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CustomerNewOrderReceived extends Notification
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

        return (new MailMessage)
            ->subject("Pesanan Berhasil Dibuat: {$order->order_number}")
            ->greeting("Halo, {$order->customer_name}!")
            ->line("Terima kasih! Pesanan Anda dengan nomor {$order->order_number} telah berhasil kami terima.")
            ->line('Produk: ' . $order->product_name_snapshot . ' × ' . $order->qty)
            ->line('Total: ' . $order->formattedTotal())
            ->line('Silakan lakukan pembayaran sesuai dengan instruksi yang tertera pada halaman pesanan.')
            ->action('Lihat Detail Pesanan & Bayar', route('order.pending', $order->public_token))
            ->line('Kami akan memproses pesanan Anda setelah pembayaran kami verifikasi.')
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
