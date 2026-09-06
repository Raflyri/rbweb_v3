<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            // A plain, non-translatable slug column — deliberately unlike
            // Article, whose slug is a per-locale JSON object holding the same
            // value four times. That design produced the locale-key mismatch
            // bugs (see Article::resolveRouteBinding); one product has one
            // canonical URL, so a string is both correct and simpler.
            $table->string('slug')->unique();

            // Translatable (spatie/laravel-translatable): {"id": "...", "en": "..."}.
            $table->json('name');
            $table->json('short_description')->nullable();
            $table->json('description')->nullable();

            // 'barang' | 'jasa' — see App\Support\ProductType.
            //
            // A string, not an ENUM: adding a value to a MySQL ENUM needs an
            // ALTER TABLE in production, which this project has already paid
            // for once (2026_04_03_172118_add_scheduled_to_articles_status_enum).
            // Validity is enforced in the form, the model and the tests instead.
            $table->string('type', 20)->index();

            // NULL price is a real state, not missing data: it means the public
            // page shows a "Hubungi Kami" button instead of a number.
            $table->decimal('price', 12, 2)->nullable();
            $table->string('currency', 3)->default('IDR');

            // NULL = stock is not tracked for this item (every service, and any
            // made-to-order goods). 0 = tracked and sold out.
            $table->unsignedInteger('stock')->nullable();

            $table->string('thumbnail')->nullable();
            $table->json('gallery')->nullable();

            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);

            // SEO, translatable — same shape as Article's meta fields.
            $table->json('meta_title')->nullable();
            $table->json('meta_description')->nullable();

            $table->timestamps();

            // The public listing always filters on is_active and orders by
            // sort_order, so give that query one index to work with.
            $table->index(['is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
