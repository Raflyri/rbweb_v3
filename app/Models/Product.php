<?php

namespace App\Models;

use App\Support\ArticleLocale;
use App\Support\ProductType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
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
     * Locales a product is actually written in.
     *
     * The site serves four languages, but the market for these items is local:
     * copy is maintained in Indonesian and English only. Visitors browsing in
     * Malay or Japanese get the fallback chain in translate() rather than an
     * empty page — unlike articles, which hide themselves in locales they were
     * never written in.
     *
     * @var array<int, string>
     */
    public const LOCALES = ['id', 'en'];

    /** Order in which translate() looks for a usable value. */
    public const LOCALE_FALLBACKS = ['id', 'en'];

    protected $fillable = [
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

    /** Indonesian name first, English second, then whatever exists. */
    public function slugSource(): string
    {
        $names = $this->getTranslations('name');

        foreach (self::LOCALE_FALLBACKS as $locale) {
            $value = $names[$locale] ?? null;

            if (is_string($value) && trim($value) !== '') {
                return $value;
            }
        }

        foreach ($names as $value) {
            if (is_string($value) && trim($value) !== '') {
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

    // ── Scopes ───────────────────────────────────────────────────────────
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
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
        return $this->type === ProductType::BARANG && $this->stock !== null;
    }

    public function isInStock(): bool
    {
        return ! $this->tracksStock() || $this->stock > 0;
    }

    // ── Type helpers ─────────────────────────────────────────────────────
    public function isBarang(): bool
    {
        return $this->type === ProductType::BARANG;
    }

    public function isJasa(): bool
    {
        return $this->type === ProductType::JASA;
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
