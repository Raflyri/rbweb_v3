<?php

namespace App\Models;

use App\Support\ArticleLocale;
use App\Support\ProductType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Schema;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Support\Str;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Spatie\Translatable\HasTranslations;

/**
 * A sellable item — physical goods ('barang') or a service ('jasa').
 *
 * Deliberately NOT modelled on Article's slug handling: the slug here is a
 * plain unique string, generated once on create by spatie/laravel-sluggable
 * and then left alone, so a permalink never moves because someone fixed a
 * typo in the product name.
 */
class Product extends Model
{
    use HasFactory, HasSlug, HasTranslations, LogsActivity;

    /**
     * Locales a product may be written in — whatever the site's language
     * switcher currently offers, so a visitor is served real copy rather than a
     * translation of convenience.
     *
     * Filling every one is never required. Unlike articles, which hide
     * themselves in locales they were never translated into, a product always
     * stays visible: translate() falls back through LOCALE_FALLBACKS rather
     * than rendering a blank page.
     *
     * Copy stored under a locale that has since been switched off is left
     * alone — translate() will still hand it back if something asks for it by
     * name — so turning a language back on brings the old text with it.
     *
     * @var array<int, string>
     */
    public const LOCALES = ArticleLocale::ENABLED;

    /** Order in which translate() looks for a usable value. */
    public const LOCALE_FALLBACKS = ['id', 'en'];

    protected $fillable = [
        'category_id',
        'slug',
        'name',
        'short_description',
        'description',
        'type',
        'price',
        'currency',
        'stock',
        'thumbnail',
        'gallery',
        'is_active',
        'is_featured',
        'sort_order',
        'meta_title',
        'meta_description',
    ];

    /** Fields stored as JSON with per-locale values. */
    public $translatable = [
        'name',
        'short_description',
        'description',
        'meta_title',
        'meta_description',
    ];

    protected $casts = [
        'price'       => 'decimal:2',
        'stock'       => 'integer',
        'gallery'     => 'array',
        'is_active'   => 'boolean',
        'is_featured' => 'boolean',
        'sort_order'  => 'integer',
    ];

    protected static function booted(): void
    {
        static::saving(function (Product $product) {
            try {
                if (! Schema::hasTable('categories')) {
                    return;
                }

                // If `type` was changed or set, keep `category_id` synced when `category_id` wasn't explicitly set to something else
                if (filled($product->type) && (! $product->isDirty('category_id') || blank($product->category_id))) {
                    $matchedCategory = Category::where('slug', $product->type)->first();
                    if ($matchedCategory) {
                        $product->category_id = $matchedCategory->id;
                    }
                } elseif (filled($product->category_id) && blank($product->type)) {
                    $matchedCategory = Category::find($product->category_id);
                    if ($matchedCategory) {
                        $product->type = $matchedCategory->slug;
                    }
                }
            } catch (\Throwable $e) {
                // Ignore if DB is not ready
            }
        });
    }

    // ── Slug ─────────────────────────────────────────────────────────────
    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(fn (Product $product): string => $product->slugSource())
            ->saveSlugsTo('slug')
            // A live URL is a promise. Renaming a product must not silently
            // break every link, QR code, and WhatsApp message pointing at it,
            // so the slug is generated once and only ever changed by hand.
            ->doNotGenerateSlugsOnUpdate();
    }

    /**
     * Indonesian name first, English second, then whatever exists.
     *
     * A candidate only counts if it actually slugifies to something. A product
     * named only in Japanese would otherwise hand Str::slug() a string of
     * characters it strips entirely, and the empty result turns into a
     * permalink like "-1" — so such a name is skipped in favour of a Latin one,
     * and 'produk' is the last resort.
     */
    public function slugSource(): string
    {
        $names = $this->getTranslations('name');

        $ordered = array_merge(
            array_filter(array_map(
                fn (string $locale) => $names[$locale] ?? null,
                self::LOCALE_FALLBACKS,
            )),
            $names,
        );

        foreach ($ordered as $value) {
            if (is_string($value) && Str::slug($value) !== '') {
                return $value;
            }
        }

        return 'produk';
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    // ── Translation helper ───────────────────────────────────────────────
    /**
     * Read a translatable field with an explicit fallback chain.
     *
     * Spatie's own fallback follows config('app.fallback_locale'), which is
     * 'en' — that would hand a Malay visitor the English name even when an
     * Indonesian one exists and reads far closer to their language. The chain
     * here is: requested locale → id → en → any non-empty value.
     */
    public function translate(string $field, ?string $locale = null): string
    {
        $values = $this->getTranslations($field);

        $candidates = array_unique(array_merge(
            [ArticleLocale::normalize($locale ?? app()->getLocale())],
            self::LOCALE_FALLBACKS,
        ));

        foreach ($candidates as $candidate) {
            $value = $values[$candidate] ?? null;

            if (is_string($value) && trim($value) !== '') {
                return $value;
            }
        }

        foreach ($values as $value) {
            if (is_string($value) && trim($value) !== '') {
                return $value;
            }
        }

        return '';
    }

    // ── Relationships ────────────────────────────────────────────────────
    /**
     * Get the category that owns the product.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    // ── Scopes ───────────────────────────────────────────────────────────
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where(function (Builder $q) use ($type) {
            $q->where('type', $type)
                ->orWhereHas('category', fn (Builder $cq) => $cq->where('slug', $type));
        });
    }

    public function scopeBarang(Builder $query): Builder
    {
        return $query->where('type', ProductType::BARANG);
    }

    public function scopeJasa(Builder $query): Builder
    {
        return $query->where('type', ProductType::JASA);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    /** The public listing order: featured first, then the manual sort. */
    public function scopeInDisplayOrder(Builder $query): Builder
    {
        return $query->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    // ── Price ────────────────────────────────────────────────────────────
    public function hasPrice(): bool
    {
        return $this->price !== null;
    }

    /**
     * "Rp 1.500.000" — or null when there is no price, which is the signal for
     * every view to render a "Hubungi Kami" call to action instead.
     *
     * Cents are shown only when they are not zero: Indonesian retail prices are
     * whole rupiah, and "Rp 250.000,00" reads like a bank statement.
     */
    public function formattedPrice(): ?string
    {
        if (! $this->hasPrice()) {
            return null;
        }

        $price    = (float) $this->price;
        $hasCents = fmod($price, 1.0) !== 0.0;
        $prefix   = $this->currency === 'IDR' ? 'Rp ' : $this->currency . ' ';

        return $prefix . number_format($price, $hasCents ? 2 : 0, ',', '.');
    }

    // ── Stock ────────────────────────────────────────────────────────────
    /** Services, and goods left with a NULL stock, are always orderable. */
    public function tracksStock(): bool
    {
        return $this->isBarang() && $this->stock !== null;
    }

    public function isInStock(): bool
    {
        return ! $this->tracksStock() || $this->stock > 0;
    }

    // ── Type helpers ─────────────────────────────────────────────────────
    public function isBarang(): bool
    {
        if ($this->type === ProductType::BARANG) {
            return true;
        }

        if ($this->type === ProductType::JASA) {
            return false;
        }

        return $this->category?->type === Category::TYPE_BARANG;
    }

    public function isJasa(): bool
    {
        if ($this->type === ProductType::JASA) {
            return true;
        }

        if ($this->type === ProductType::BARANG) {
            return false;
        }

        return $this->category?->type === Category::TYPE_JASA;
    }

    // ── Activity Log Config ──────────────────────────────────────────────
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('product');
    }
}
