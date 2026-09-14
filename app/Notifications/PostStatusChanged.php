<?php

namespace App\Notifications;

use App\Models\Post;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class PostStatusChanged extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Post $post,
        public readonly string $newStatus,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): \Illuminate\Notifications\Messages\MailMessage
    {
        $postTitle = is_array($this->post->title)
            ? ($this->post->title['id'] ?? $this->post->title['en'] ?? 'Artikel Kamu')
            : (string) $this->post->title;
            
        $mail = (new \Illuminate\Notifications\Messages\MailMessage)
            ->greeting("Halo, {$notifiable->name}!");

        if ($this->newStatus === Post::STATUS_PUBLISHED) {
            $mail->subject("Artikel Anda \"{$postTitle}\" telah Dipublikasikan")
                 ->line("Selamat! Artikel Anda yang berjudul \"{$postTitle}\" telah disetujui dan diterbitkan oleh Admin.")
                 ->action('Lihat Artikel', url('/'));
        } elseif ($this->newStatus === Post::STATUS_REJECTED) {
            $mail->subject("Artikel Anda \"{$postTitle}\" Ditolak")
                 ->line("Maaf, artikel Anda yang berjudul \"{$postTitle}\" belum dapat dipublikasikan saat ini.")
                 ->line("Silakan masuk ke panel untuk melihat apakah ada catatan dari Admin.")
                 ->action('Buka Panel', url('/rbdashboard'));
        } else {
            $mail->subject("Status Artikel Berubah")
                 ->line("Status artikel \"{$postTitle}\" kini menjadi {$this->newStatus}.");
        }

        return $mail->salutation("Salam,\n" . config('app.name'));
    }

    public function toDatabase(object $notifiable): array
    {
        $title = match ($this->newStatus) {
            Post::STATUS_PUBLISHED => '🎉 Artikel Kamu Dipublikasikan!',
            Post::STATUS_REJECTED  => '❌ Artikel Kamu Ditolak',
            default                => 'Status Artikel Berubah',
        };

        $postTitle = is_array($this->post->title)
            ? ($this->post->title['id'] ?? $this->post->title['en'] ?? 'Artikel Kamu')
            : (string) $this->post->title;

        $body = match ($this->newStatus) {
            Post::STATUS_PUBLISHED => "Artikel \"{$postTitle}\" telah dipublikasikan oleh Admin.",
            Post::STATUS_REJECTED  => "Artikel \"{$postTitle}\" tidak dapat dipublikasikan. Silakan tinjau dan edit kembali.",
            default                => "Status artikel \"{$postTitle}\" berubah menjadi {$this->newStatus}.",
        };

        $icon  = $this->newStatus === Post::STATUS_PUBLISHED ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle';
        $color = $this->newStatus === Post::STATUS_PUBLISHED ? 'success' : 'danger';

        // Format kompatibel dengan Filament database notifications
        return FilamentNotification::make()
            ->title($title)
            ->body($body)
            ->icon($icon)
            ->color($color)
            ->getDatabaseMessage();
    }
}
