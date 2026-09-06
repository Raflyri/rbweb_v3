<?php

namespace App\Support;

/**
 * Where an order sits in the fulfilment flow.
 *
 * Stored as a plain string column, not a MySQL ENUM: adding a value to an ENUM
 * costs an ALTER TABLE on the live database, which this project has already
 * paid for once (2026_04_03_172118_add_scheduled_to_articles_status_enum).
 * This class is the single source of truth instead.
 */
class OrderStatus
{
    /** Just came in, nobody has looked at it yet. */
    public const BARU = 'baru';

    /** Acknowledged and being worked on. */
    public const DIPROSES = 'diproses';

    /** Delivered / performed, nothing left to do. */
    public const SELESAI = 'selesai';

    public const DIBATALKAN = 'dibatalkan';

    public const DEFAULT = self::BARU;

    /** @var array<int, string> */
    public const OPTIONS = [self::BARU, self::DIPROSES, self::SELESAI, self::DIBATALKAN];

    /** Filament Select / table-ready [value => label] map. */
    public static function options(): array
    {
        return [
            self::BARU       => 'Baru',
            self::DIPROSES   => 'Diproses',
            self::SELESAI    => 'Selesai',
            self::DIBATALKAN => 'Dibatalkan',
        ];
    }

    public static function label(?string $status): string
    {
        return self::options()[$status] ?? '—';
    }

    public static function isValid(?string $status): bool
    {
        return in_array($status, self::OPTIONS, true);
    }

    public static function color(?string $status): string
    {
        return match ($status) {
            self::BARU       => 'warning',
            self::DIPROSES   => 'info',
            self::SELESAI    => 'success',
            self::DIBATALKAN => 'danger',
            default          => 'gray',
        };
    }
}
