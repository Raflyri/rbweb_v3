<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Fix: kolom `slug` di tabel articles harus bertipe JSON karena merupakan
 * field translatable (multi-locale). Error SQLSTATE[22001] terjadi karena
 * kolom masih bertipe VARCHAR(255) yang terlalu kecil untuk JSON object.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Cek tipe kolom saat ini secara langsung dari information_schema
        $columnType = DB::selectOne("
            SELECT DATA_TYPE
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'articles'
              AND COLUMN_NAME = 'slug'
        ");

        // Hanya ubah jika kolom bukan json/longtext (MySQL menyimpan JSON sebagai longtext)
        if ($columnType && !in_array(strtolower($columnType->DATA_TYPE), ['json', 'longtext'])) {

            // 1. Drop constraints yang bergantung pada kolom slug (unique index & virtual column)
            try {
                Schema::table('articles', function (Blueprint $table) {
                    $table->dropUnique('articles_slug_id_virtual_unique');
                });
            } catch (\Exception $e) {
                // Constraint tidak ada, lanjutkan
            }

            try {
                Schema::table('articles', function (Blueprint $table) {
                    $table->dropColumn('slug_id_virtual');
                });
            } catch (\Exception $e) {
                // Kolom tidak ada, lanjutkan
            }

            try {
                Schema::table('articles', function (Blueprint $table) {
                    $table->dropUnique(['slug']);
                });
            } catch (\Exception $e) {
                // Constraint tidak ada, lanjutkan
            }

            // 2. Konversi data slug yang masih plain-string ke JSON
            $articles = DB::table('articles')->get(['id', 'slug']);
            foreach ($articles as $article) {
                if ($article->slug) {
                    json_decode($article->slug);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        // String biasa → wrap ke JSON dengan locale 'id'
                        DB::table('articles')
                            ->where('id', $article->id)
                            ->update(['slug' => json_encode(['id' => $article->slug])]);
                    }
                }
            }

            // 3. Ubah tipe kolom slug menjadi JSON
            Schema::table('articles', function (Blueprint $table) {
                $table->json('slug')->nullable()->change();
            });

            // 4. Tambahkan kembali virtual column & unique index untuk slug bahasa ID
            Schema::table('articles', function (Blueprint $table) {
                $table->string('slug_id_virtual')
                      ->virtualAs("JSON_UNQUOTE(JSON_EXTRACT(slug, '$.id'))")
                      ->nullable();
                $table->unique('slug_id_virtual', 'articles_slug_id_virtual_unique');
            });
        }
    }

    public function down(): void
    {
        // Revert: hapus virtual column, kembalikan slug ke VARCHAR(255)
        try {
            Schema::table('articles', function (Blueprint $table) {
                $table->dropUnique('articles_slug_id_virtual_unique');
                $table->dropColumn('slug_id_virtual');
            });
        } catch (\Exception $e) {}

        Schema::table('articles', function (Blueprint $table) {
            $table->string('slug')->nullable()->change();
        });
    }
};
