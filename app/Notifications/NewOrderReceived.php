<?php

namespace App\Notifications;

use App\Models\Order;
use App\Support\OrderStatus;
use App\Support\ProductType;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * "A new order just came in" — sent to the shop's own address.
 *
 * Deliberately does NOT implement ShouldQueue. This host has no dependable
 * queue worker (the sitemap was five months stale for exactly that reason), so
 * a queued notification would be a row in `jobs` that nobody ever sees. The
 * caller wraps the send in a try/catch, so a slow or broken SMTP server delays
 * a response at worst — it never loses the order.
 */
class NewOrderReceived extends Notification
{
    public function __construct(protected Order $order) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $order = $this->order;

        $mail = (new MailMessage)
            ->subject("Pesanan baru {$order->order_number} — {$order->product_name_snapshot}")
            ->greeting('Ada pesanan baru masuk.')
            ->line("Nomor pesanan: {$order->order_number}")
            ->line("Produk: {$order->product_name_snapshot} (" . ProductType::label($order->product_type_snapshot) . ')')
            ->line("Jumlah: {$order->qty}")
            ->line('Subtotal: ' . $order->formattedSubtotal())
            ->line('Status: ' . OrderStatus::label($order->status))
            ->line('---')
            ->line("Nama: {$order->customer_name}")
            ->line("Email: {$order->customer_email}")
            ->line("WhatsApp/HP: {$order->customer_phone}");

        if ($order->shipping_address) {
            $mail->line('Alamat pengiriman: ' . $order->shipping_address);
            $mail->line('Ongkos kirim belum dihitung — konfirmasikan ke pembeli, lalu isi di panel admin.');
        }

        if ($order->preferred_date) {
            $mail->line('Tanggal yang diinginkan: ' . $order->preferred_date->format('d/m/Y'));
        }

        if ($order->notes) {
            $mail->line('Catatan pembeli: ' . $order->notes);
        }

        return $mail
            ->action('Buka di Panel Admin', url('/rbdashboard/orders'))
            ->salutation("Salam,\n" . config('app.name'));
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return [
            'order_id'     => $this->order->id,
            'order_number' => $this->order->order_number,
        ];
    }
}
