<?php

namespace App\Services;

use Spatie\Permission\Models\Role;

class RoleLimitService
{
    /**
     * Batas maksimum jumlah user per role.
     * null = tidak ada batas.
     */
    public static array $limits = [
        'super_admin'  => 2,
        'admin'        => 20,
        'premium'      => null,
        'regular_user' => null,
    ];

    /**
     * Cek apakah role masih bisa ditambahkan (belum mencapai batas).
     */
    public static function canAssign(string $roleName): bool
    {
        $limit = self::$limits[$roleName] ?? null;

        if ($limit === null) {
            return true; // Tidak ada batas
        }

        $currentCount = Role::findByName($roleName, 'web')
            ->users()
            ->count();

        return $currentCount < $limit;
    }

    /**
     * Kembalikan sisa slot yang tersedia untuk sebuah role.
     * null = unlimited.
     */
    public static function remainingSlots(string $roleName): ?int
    {
        $limit = self::$limits[$roleName] ?? null;

        if ($limit === null) {
            return null;
        }

        $currentCount = Role::findByName($roleName, 'web')
            ->users()
            ->count();

        return max(0, $limit - $currentCount);
    }

    /**
     * Kembalikan pesan error jika batas tercapai.
     */
    public static function limitErrorMessage(string $roleName): string
    {
        $limit = self::$limits[$roleName] ?? 0;
        return "Role '{$roleName}' sudah mencapai batas maksimum ({$limit} user). Tidak bisa menambahkan lebih banyak.";
    }
}
