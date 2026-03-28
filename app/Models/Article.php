<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Article extends Model
{
    use HasFactory, \Spatie\Translatable\HasTranslations, LogsActivity;

    protected $guarded = [];

    /** Fields stored as JSON with per-locale values. */
    public $translatable = ['title', 'content'];

    protected $casts = [
        'published_at' => 'datetime',
        'reviewed_at'  => 'datetime',
    ];

    // ── Activity Log Config ──────────────────────────────────────────────
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('article');
    }

    // ── Auto-generate slug from title if not provided ────────────────────
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Article $article) {
            if (empty($article->slug)) {
                $article->slug = static::generateUniqueSlug(
                    $article->getTranslation('title', 'en', true) ?: 'article'
                );
            }
            // Always force Pending Review on creation (clients cannot self-publish)
            if (empty($article->status)) {
                $article->status = 'Pending Review';
            }
        });
    }

    protected static function generateUniqueSlug(string $title): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $i    = 1;

        while (static::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }

    // ── Relationships ─────────────────────────────────────────────────────
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    // ── Scopes ───────────────────────────────────────────────────────────
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'Published');
    }

    public function scopePendingReview(Builder $query): Builder
    {
        return $query->where('status', 'Pending Review');
    }

    // ── Helpers ──────────────────────────────────────────────────────────
    public function isPendingReview(): bool
    {
        return $this->status === 'Pending Review';
    }

    public function isPublished(): bool
    {
        return $this->status === 'Published';
    }

    public function isDraft(): bool
    {
        return $this->status === 'Draft';
    }
}
