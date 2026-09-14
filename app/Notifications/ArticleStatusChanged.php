<?php

namespace App\Notifications;

use App\Models\Article;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Filament\Notifications\Notification as FilamentNotification;

class ArticleStatusChanged extends Notification
{
    use Queueable;

    public function __construct(
        protected Article $article,
        protected string $action   // 'approved' | 'rejected'
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): \Illuminate\Notifications\Messages\MailMessage
    {
        $title = $this->article->getTranslation('title', 'en', true) ?? 'Artikel';
        
        $mail = (new \Illuminate\Notifications\Messages\MailMessage)
            ->greeting("Halo, {$notifiable->name}!");

        if ($this->action === 'approved') {
            $mail->subject("Artikel Anda \"{$title}\" telah Dipublikasikan")
                 ->line("Selamat! Artikel Anda yang berjudul \"{$title}\" telah disetujui dan kini dapat dilihat oleh publik.")
                 ->action('Lihat Artikel', url('/'));
        } else {
            $mail->subject("Artikel Anda \"{$title}\" Perlu Revisi")
                 ->line("Artikel Anda yang berjudul \"{$title}\" telah dikembalikan ke Draft.")
                 ->line("Silakan tinjau kembali dan lakukan perbaikan sebelum mengirim ulang.")
                 ->action('Buka Dashboard', url('/rbdashboard'));
        }

        return $mail->salutation("Salam,\n" . config('app.name'));
    }

    public function toArray(object $notifiable): array
    {
        $title = $this->article->getTranslation('title', 'en', true);

        return FilamentNotification::make()
            ->title($this->action === 'approved'
                ? "Your article \"{$title}\" has been published! 🎉"
                : "Your article \"{$title}\" needs revisions."
            )
            ->body($this->action === 'approved'
                ? 'It is now live and visible to the public.'
                : 'It has been returned to Draft. Please make changes and resubmit.'
            )
            ->icon($this->action === 'approved' ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle')
            ->iconColor($this->action === 'approved' ? 'success' : 'danger')
            ->getDatabaseMessage();
    }
}
