<?php

namespace App\Support;

/**
 * The two kinds of thing RBeverything sells.
 *
 * Kept as a tag on one Product model rather than two models: barang and jasa
 * share every field except shipping, and /produk-layanan stays a single
 * listing that can be filtered by tab.
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

    /** Filament Select / public tab-ready [value => label] map. */
    public static function options(?string $locale = null): array
    {
        $loc = ArticleLocale::normalize($locale ?: app()->getLocale());

        if ($loc === 'en') {
            return [
                self::BARANG => 'Products',
                self::JASA   => 'Services',
            ];
        }

        return [
            self::BARANG => 'Barang',
            self::JASA   => 'Jasa',
        ];
    }

    public static function label(?string $type, ?string $locale = null): string
    {
        return self::options($locale)[$type] ?? '—';
    }

    public static function isValid(?string $type): bool
    {
        return in_array($type, self::OPTIONS, true);
    }

    /** Badge colour per type — one place, used by Filament and the public views. */
    public static function color(?string $type): string
    {
        return match ($type) {
            self::JASA => 'info',
            default    => 'warning', // barang
        };
    }
}
