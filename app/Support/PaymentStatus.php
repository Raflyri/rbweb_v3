<?php

namespace App\Support;

/**
 * Where the money for an order stands.
 *
 * Kept separate from OrderStatus on purpose: an order can be 'diproses' while
 * payment is still 'menunggu', and conflating the two is how "paid but not
 * shipped" becomes unrepresentable.
 *
 * A plain string column for the same reason as OrderStatus — phase 4 adds
 * 'menunggu_verifikasi' when buyers can upload a transfer receipt, and that
 * must not require an ALTER TABLE on the live database.
 */
class PaymentStatus
{
    /** Nothing received yet. */
    public const MENUNGGU = 'menunggu';

    /**
     * Buyer says they have paid and uploaded proof; an admin still has to look.
     * Not reachable until phase 4 builds the upload, defined here so the column
     * never needs widening later.
     */
    public const MENUNGGU_VERIFIKASI = 'menunggu_verifikasi';

    public const LUNAS = 'lunas';

    public const GAGAL = 'gagal';

    public const DIBATALKAN = 'dibatalkan';

    public const DEFAULT = self::MENUNGGU;

    /** @var array<int, string> */
    public const OPTIONS = [
        self::MENUNGGU,
        self::MENUNGGU_VERIFIKASI,
        self::LUNAS,
        self::GAGAL,
        self::DIBATALKAN,
    ];

    public static function options(): array
    {
        return [
            self::MENUNGGU            => 'Menunggu Pembayaran',
            self::MENUNGGU_VERIFIKASI => 'Menunggu Verifikasi',
            self::LUNAS               => 'Lunas',
            self::GAGAL               => 'Gagal',
            self::DIBATALKAN          => 'Dibatalkan',
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
            self::LUNAS               => 'success',
            self::MENUNGGU_VERIFIKASI => 'info',
            self::GAGAL               => 'danger',
            self::DIBATALKAN          => 'gray',
            default                   => 'warning', // menunggu
        };
    }
}
