<?php

namespace App\Observers;

use App\Models\Post;
use App\Notifications\PostStatusChanged;

class PostObserver
{
    /**
     * Dipanggil setelah post berhasil diperbarui.
     * Mengirim notifikasi ke pemilik artikel jika status berubah ke Published atau Rejected.
     */
    public function updated(Post $post): void
    {
        // Hanya proses jika kolom 'status' yang berubah
        if (! $post->wasChanged('status')) {
            return;
        }

        $newStatus = $post->status;

        // Hanya kirim notifikasi untuk Published atau Rejected (bukan Draft/Pending)
        if (! in_array($newStatus, [Post::STATUS_PUBLISHED, Post::STATUS_REJECTED])) {
            return;
        }

        // Kirim notifikasi ke pemilik artikel
        if ($post->user) {
            $post->user->notify(new PostStatusChanged($post, $newStatus));
        }
    }
}
