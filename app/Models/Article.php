<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Article extends Model
{
    use HasFactory, \Spatie\Translatable\HasTranslations;

    protected $guarded = [];

    /** Fields stored as JSON with per-locale values. */
    public $translatable = ['title', 'content'];

    protected $casts = [
        'published_at' => 'datetime',
    ];

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

    // ── Scopes ───────────────────────────────────────────────────────────
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'Published');
    }
}
