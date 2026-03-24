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
        return ['database'];
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
