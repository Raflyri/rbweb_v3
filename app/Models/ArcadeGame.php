<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ArcadeGame extends Model
{
    use HasFactory;

    public const STATUS_TAYANGKAN = 'Tayangkan';
    public const STATUS_DRAFT     = 'Draft';
    public const STATUSES         = [self::STATUS_TAYANGKAN, self::STATUS_DRAFT];

    protected $fillable = [
        'title',
        'slug',
        'description',
        'thumbnail',
        'game_url',
        'game_category_id',
        'status',
        'is_spotlight',
    ];

    protected $casts = [
        'is_spotlight' => 'boolean',
    ];

    /**
     * Relationship: An ArcadeGame belongs to a GameCategory.
     */
    public function category(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(GameCategory::class, 'game_category_id');
    }

    /**
     * Scope to only spotlight games.
     */
    public function scopeSpotlight(Builder $query): Builder
    {
        return $query->where('is_spotlight', true);
    }

    protected static function booted(): void
    {
        static::creating(function (ArcadeGame $game) {
            if (blank($game->slug) && filled($game->title)) {
                $game->slug = static::generateUniqueSlug($game->title);
            }
        });
    }

    public static function generateUniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'game';
        $slug = $base;
        $counter = 1;

        while (
            static::query()
                ->where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    /**
     * Scope to only published ('Tayangkan') arcade games.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_TAYANGKAN);
    }
}
