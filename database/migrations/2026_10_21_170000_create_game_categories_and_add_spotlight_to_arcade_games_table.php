<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('game_categories')) {
            Schema::create('game_categories', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->timestamps();
            });

            // Seed initial game categories
            $now = now();
            DB::table('game_categories')->insert([
                ['name' => 'Puzzle & Logika',   'slug' => 'puzzle-logika',   'created_at' => $now, 'updated_at' => $now],
                ['name' => 'Retro Arcade',     'slug' => 'retro-arcade',     'created_at' => $now, 'updated_at' => $now],
                ['name' => 'Edukasi & Anak',   'slug' => 'edukasi-anak',   'created_at' => $now, 'updated_at' => $now],
                ['name' => 'Aksi & Petualangan', 'slug' => 'aksi-petualangan', 'created_at' => $now, 'updated_at' => $now],
            ]);
        }

        Schema::table('arcade_games', function (Blueprint $table) {
            if (!Schema::hasColumn('arcade_games', 'game_category_id')) {
                $table->foreignId('game_category_id')
                    ->nullable()
                    ->after('thumbnail')
                    ->constrained('game_categories')
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('arcade_games', 'is_spotlight')) {
                $table->boolean('is_spotlight')
                    ->default(false)
                    ->after('status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('arcade_games', function (Blueprint $table) {
            if (Schema::hasColumn('arcade_games', 'game_category_id')) {
                $table->dropForeign(['game_category_id']);
                $table->dropColumn('game_category_id');
            }

            if (Schema::hasColumn('arcade_games', 'is_spotlight')) {
                $table->dropColumn('is_spotlight');
            }
        });

        Schema::dropIfExists('game_categories');
    }
};
