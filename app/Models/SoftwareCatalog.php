<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class SoftwareCatalog extends Model
{
    use HasFactory;

    public const STATUS_TAYANGKAN = 'Tayangkan';
    public const STATUS_DRAFT     = 'Draft';
    public const STATUSES         = [self::STATUS_TAYANGKAN, self::STATUS_DRAFT];

    protected $fillable = [
        'title',
        'slug',
        'software_category_id',
        'category', // Fallback string for compatibility
        'version',
        'release_date',
        'badge_text',
        'short_description',
        'description',
        'tech_stack',
        'features',
        'changelog',
        'thumbnail',
        'demo_url',
        'demo_type',
        'demo_credentials',
        'status',
        'is_featured',
        'sort_order',
    ];

    protected $casts = [
        'tech_stack'   => 'array',
        'features'     => 'array',
        'is_featured'  => 'boolean',
        'sort_order'   => 'integer',
        'release_date' => 'date',
    ];

    /**
     * Relationship: Software belongs to a dynamic category.
     */
    public function softwareCategory(): BelongsTo
    {
        return $this->belongsTo(SoftwareCategory::class, 'software_category_id');
    }

    /**
     * Dynamic category name accessor with fallback.
     */
    public function getCategoryNameAttribute(): string
    {
        return $this->softwareCategory?->name ?? $this->category ?? 'Umum';
    }

    /**
     * Scope to only published ('Tayangkan') software items.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_TAYANGKAN);
    }

    /**
     * Scope to only featured software items.
     */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    protected static function booted(): void
    {
        static::creating(function (SoftwareCatalog $item) {
            if (blank($item->slug) && filled($item->title)) {
                $item->slug = static::generateUniqueSlug($item->title);
            }
        });
    }

    public static function generateUniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'software';
        $slug = $base;
        $counter = 2;

        while (static::where('slug', $slug)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()
        ) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}
