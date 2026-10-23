<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Create software_categories table
        Schema::create('software_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // 2. Add software_category_id, release_date, and changelog to software_catalogs
        Schema::table('software_catalogs', function (Blueprint $table) {
            $table->foreignId('software_category_id')
                ->nullable()
                ->after('slug')
                ->constrained('software_categories')
                ->nullOnDelete();
            $table->date('release_date')->nullable()->after('version');
            $table->text('changelog')->nullable()->after('features');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('software_catalogs', function (Blueprint $table) {
            $table->dropForeign(['software_category_id']);
            $table->dropColumn(['software_category_id', 'release_date', 'changelog']);
        });

        Schema::dropIfExists('software_categories');
    }
};
