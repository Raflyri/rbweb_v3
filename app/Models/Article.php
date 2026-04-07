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

    protected $fillable = [
        'user_id',
        'reviewer_id',
        'title',
        'slug',
        'content',
        'excerpt',
        'meta_title',
        'meta_description',
        'thumbnail',
        'status',
        'published_at',
        'reviewed_at'
    ];

    /** Fields stored as JSON with per-locale values. */
    public $translatable = [
        'title',
        'slug',
        'content',
        'excerpt',
        'meta_title',
        'meta_description',
    ];

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
            // Build a slug JSON object covering every translatable locale.
            // The slug column is stored as JSON by Spatie Translatable, so
            // we must query it with whereJsonContains, not a plain where().
            if (empty($article->slug) || $article->slug === '{}' || $article->slug === 'null') {
                $titleEn = $article->getTranslation('title', 'en', false)
                        ?: $article->getTranslation('title', 'id', false)
                        ?: 'article';

                $baseSlug = static::generateUniqueSlug($titleEn);

                // Store the same slug for every locale so all URL lookups work.
                $article->slug = array_fill_keys(
                    $article->translatable,
                    null
                );
                // Only slug is relevant here:
                $locales = ['id', 'my', 'en', 'jp', 'ms', 'ja'];
                $slugMap = [];
                foreach ($locales as $locale) {
                    $slugMap[$locale] = $baseSlug;
                }
                $article->slug = $slugMap;
            }
        });
    }

    /**
     * Generate a slug that is unique across all locales stored in the JSON column.
     *
     * @param  string   $title     Source text to slugify.
     * @param  int|null $excludeId Article ID to exclude (for update scenarios).
     */
    public static function generateUniqueSlug(string $title, ?int $excludeId = null): string
    {
        $base = Str::slug($title) ?: 'article';
        $slug = $base;
        $i    = 1;

        while (static::slugExists($slug, $excludeId)) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }

    /**
     * Check if any row already uses $slug in any locale of the JSON slug column.
     */
    protected static function slugExists(string $slug, ?int $excludeId = null): bool
    {
        $query = static::where(function ($q) use ($slug) {
            // Check each common locale key inside the JSON column
            foreach (['id', 'my', 'en', 'jp', 'ms', 'ja'] as $locale) {
                $q->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(slug, '$.{$locale}')) = ?", [$slug]);
            }
        });

        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
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

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
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

    public function isScheduled(): bool
    {
        return $this->status === 'Scheduled';
    }

    // ── Route Model Binding ───────────────────────────────────────────────
    /**
     * Override the default route binding so that {article} route parameters
     * containing a slug string are resolved correctly from the JSON column.
     *
     * Laravel's default binding does WHERE slug = $value which never matches
     * because the column stores JSON like {"id":"my-slug","en":"my-slug",...}.
     *
     * We search across all supported locale keys using MySQL's arrow operator.
     *
     * @param  mixed       $value  The slug value from the URL segment.
     * @param  string|null $field  The binding field (null → uses getRouteKeyName).
     */
    public function resolveRouteBinding($value, $field = null): ?self
    {
        $locale  = app()->getLocale();
        $locales = ['id', 'my', 'en', 'jp', 'ms', 'ja'];

        return $this->where(function ($query) use ($value, $locale, $locales) {
                // Try active locale first, then all others as fallback.
                $query->where("slug->{$locale}", $value);

                foreach ($locales as $loc) {
                    if ($loc !== $locale) {
                        $query->orWhere("slug->{$loc}", $value);
                    }
                }
            })
            ->firstOrFail();
    }
}
