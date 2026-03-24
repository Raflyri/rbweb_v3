<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Translatable\HasTranslations;

class Post extends Model
{
    use HasTranslations;

    protected $guarded = [];

    public $translatable = ['title', 'content'];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    // ✅ Konstanta status untuk konsistensi di seluruh aplikasi
    const STATUS_DRAFT     = 'draft';
    const STATUS_PENDING   = 'pending_review';
    const STATUS_PUBLISHED = 'published';
    const STATUS_REJECTED  = 'rejected';

    public static function statusOptions(): array
    {
        return [
            self::STATUS_DRAFT     => 'Draft',
            self::STATUS_PENDING   => 'Pending Review',
            self::STATUS_PUBLISHED => 'Published',
            self::STATUS_REJECTED  => 'Rejected',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
