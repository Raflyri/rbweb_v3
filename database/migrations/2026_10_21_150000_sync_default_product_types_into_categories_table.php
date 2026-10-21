<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * 1. Widens `products.type` from VARCHAR(20) to VARCHAR(255) so dynamic
     *    product types (categories) with longer slugs can be stored safely.
     * 2. Seeds the default 'Barang' and 'Jasa' rows into `categories` if they
     *    do not already exist.
     * 3. Links existing `products` rows to their matching `categories` record
     *    without modifying or deleting any existing product data.
     */
    public function up(): void
    {
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                $table->string('type', 255)->change();
            });
        }

        if (! Schema::hasTable('categories')) {
            return;
        }

        $now = now();

        $defaults = [
            ['name' => 'Barang', 'slug' => 'barang', 'type' => 'Barang'],
            ['name' => 'Jasa',   'slug' => 'jasa',   'type' => 'Jasa'],
        ];

        foreach ($defaults as $default) {
            $exists = DB::table('categories')->where('slug', $default['slug'])->exists();
            if (! $exists) {
                DB::table('categories')->insert([
                    'name'       => $default['name'],
                    'slug'       => $default['slug'],
                    'type'       => $default['type'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        if (Schema::hasTable('products')) {
            // Also ensure any custom `type` strings already on products exist in `categories`
            $existingTypes = DB::table('products')
                ->whereNotNull('type')
                ->where('type', '!=', '')
                ->distinct()
                ->pluck('type');

            foreach ($existingTypes as $typeValue) {
                $slug = Str::slug($typeValue) ?: strtolower($typeValue);
                if (! DB::table('categories')->where('slug', $slug)->exists()) {
                    DB::table('categories')->insert([
                        'name'       => ucfirst($typeValue),
                        'slug'       => $slug,
                        'type'       => strtolower($typeValue) === 'jasa' ? 'Jasa' : 'Barang',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }

            // Link existing products to their corresponding category_id
            $categories = DB::table('categories')->pluck('id', 'slug');
            foreach ($categories as $slug => $categoryId) {
                DB::table('products')
                    ->whereNull('category_id')
                    ->where('type', $slug)
                    ->update(['category_id' => $categoryId]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Non-destructive down migration to preserve production data
    }
};
