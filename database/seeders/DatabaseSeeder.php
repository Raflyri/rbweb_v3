<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // ✅ Seed semua role terlebih dahulu
        $this->call(RoleSeeder::class);

        // User admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@rbeverything.com'],
            ['name' => 'RB Admin', 'password' => bcrypt('password')]
        );
        // Only assign role if the user doesn't already have it.
        // Using syncRoles() is forbidden — it would strip any extra roles
        // granted manually in production.
        if (! $admin->hasRole('super_admin')) {
            $admin->assignRole('super_admin');
        }

        // User regular (untuk testing Client Area)
        $regular = User::firstOrCreate(
            ['email' => 'user@rbeverything.com'],
            ['name' => 'Test User', 'password' => bcrypt('password')]
        );
        if (! $regular->hasRole('regular_user')) {
            $regular->assignRole('regular_user');
        }
    }
}
