<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Tells the buyer what happened to the payment they said they made.
 *
 * Both outcomes are worth an email. A confirmation closes the loop; a
 * rejection is the only way they learn the receipt did not check out — without
 * it they sit waiting for goods nobody is preparing.
 *
 * Not queued, for the same reason as NewOrderReceived: this host has no
 * dependable queue worker, so a queued mail is a row in `jobs` nobody reads.
 */
class PaymentStatusUpdated extends Notification
{
    public const CONFIRMED = 'confirmed';

    public const REJECTED = 'rejected';

    public function __construct(
        protected Order $order,
        protected string $outcome,
        protected ?string $reason = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $order = $this->order;

        return $this->outcome === self::CONFIRMED
            ? $this->confirmedMail($order)
            : $this->rejectedMail($order);
    }

    protected function confirmedMail(Order $order): MailMessage
    {
        return (new MailMessage)
            ->subject("Pembayaran pesanan {$order->order_number} sudah kami terima")
            ->greeting("Halo, {$order->customer_name}!")
            ->line("Pembayaran untuk pesanan {$order->order_number} sudah kami konfirmasi. Terima kasih.")
            ->line('Produk: ' . $order->product_name_snapshot . ' × ' . $order->qty)
            ->line('Total dibayar: ' . $order->formattedTotal())
            ->line($order->needsShipping()
                ? 'Pesanan kamu akan segera kami siapkan dan kirim.'
                : 'Kami akan menghubungi kamu untuk pelaksanaan layanannya.')
            ->action('Lihat Pesanan', route('order.pending', $order->public_token))
            ->salutation("Salam,\n" . config('app.name'));
    }

    protected function rejectedMail(Order $order): MailMessage
    {
        $mail = (new MailMessage)
            ->subject("Bukti transfer pesanan {$order->order_number} perlu diperbaiki")
            ->greeting("Halo, {$order->customer_name}!")
            ->line("Kami belum bisa memverifikasi bukti transfer untuk pesanan {$order->order_number}.");

        if (filled($this->reason)) {
            $mail->line('Catatan dari kami: ' . $this->reason);
        }

        return $mail
            ->line('Silakan unggah ulang bukti transfer lewat halaman pesanan kamu.')
            ->action('Unggah Ulang Bukti Transfer', route('order.pending', $order->public_token))
            ->salutation("Salam,\n" . config('app.name'));
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return [
            'order_number' => $this->order->order_number,
            'outcome'      => $this->outcome,
        ];
    }
}
