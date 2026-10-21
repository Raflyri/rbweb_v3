<?php

namespace App\Support;

use App\Models\Category;
use Illuminate\Support\Facades\Schema;

/**
 * Product types ("Jenis Produk") sold on RBeverything.
 *
 * Includes the two built-in defaults ('barang' and 'jasa') and dynamically
 * loads any additional product types stored in the `categories` database table.
 */
class ProductType
{
    /** Physical goods — shippable, may track stock. */
    public const BARANG = 'barang';

    /** Services — nothing to ship, stock never applies. */
    public const JASA = 'jasa';

    public const DEFAULT = self::BARANG;

    /** @var array<int, string> */
    public const OPTIONS = [self::BARANG, self::JASA];

    /**
     * Filament Select / public tab-ready [value => label] map.
     * Merges built-in defaults with dynamic items from the `categories` table.
     */
    public static function options(?string $locale = null): array
    {
        $loc = ArticleLocale::normalize($locale ?: app()->getLocale());

        $options = $loc === 'en'
            ? [
                self::BARANG => 'Products',
                self::JASA   => 'Services',
            ]
            : [
                self::BARANG => 'Barang',
                self::JASA   => 'Jasa',
            ];

        try {
            if (Schema::hasTable('categories')) {
                $dbCategories = Category::orderBy('id')->pluck('name', 'slug')->all();

                foreach ($dbCategories as $slug => $name) {
                    if ($loc === 'en' && in_array($slug, [self::BARANG, self::JASA], true)) {
                        continue;
                    }
                    $options[$slug] = $name;
                }
            }
        } catch (\Throwable $e) {
            // Gracefully fall back to built-in options if DB is unavailable
        }

        return $options;
    }

    public static function label(?string $type, ?string $locale = null): string
    {
        if ($type === null || $type === '') {
            return '—';
        }

        $options = self::options($locale);

        if (isset($options[$type])) {
            return $options[$type];
        }

        return ucfirst(str_replace('-', ' ', $type));
    }

    public static function isValid(?string $type): bool
    {
        if ($type === null || $type === '') {
            return false;
        }

        if (in_array($type, self::OPTIONS, true)) {
            return true;
        }

        try {
            return Schema::hasTable('categories') && Category::where('slug', $type)->exists();
        } catch (\Throwable $e) {
            return false;
        }
    }

    /** Badge colour per type — used by Filament and the public views. */
    public static function color(?string $type): string
    {
        if ($type === self::JASA) {
            return 'info';
        }

        if ($type === self::BARANG) {
            return 'warning';
        }

        try {
            if ($type && Schema::hasTable('categories')) {
                $catType = Category::where('slug', $type)->value('type');

                return $catType === Category::TYPE_JASA ? 'info' : 'warning';
            }
        } catch (\Throwable $e) {
            // Fallback below
        }

        return 'warning';
    }
}
