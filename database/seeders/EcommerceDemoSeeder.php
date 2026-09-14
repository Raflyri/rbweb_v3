<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * MASTER DEMO SEEDER FOR E-COMMERCE (PRODUCTS & TRANSACTIONS).
 *
 * For local development and staging environments only.
 * Runs ProductDemoSeeder followed by OrderDemoSeeder.
 *
 * Run manually via:
 *
 *     php artisan db:seed --class=EcommerceDemoSeeder
 */
class EcommerceDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            $this->command?->error('EcommerceDemoSeeder is sample data and refuses to run in production.');

            return;
        }

        $this->command?->info('Seeding demo e-commerce data (Products & Transactions)...');

        $this->call(ProductDemoSeeder::class);
        $this->call(OrderDemoSeeder::class);

        $this->command?->info('E-commerce demo data seeding complete!');
    }
}
