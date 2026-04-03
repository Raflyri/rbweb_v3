<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Pastikan kolom yang belum ada dibuat terlebih dahulu
        Schema::table('articles', function (Blueprint $table) {
            if (!Schema::hasColumn('articles', 'excerpt')) {
                $table->text('excerpt')->nullable()->after('content');
            }
            if (!Schema::hasColumn('articles', 'meta_title')) {
                $table->string('meta_title')->nullable()->after('excerpt');
            }
            if (!Schema::hasColumn('articles', 'meta_description')) {
                $table->text('meta_description')->nullable()->after('meta_title');
            }
        });

        // Amankan dan transformasikan data string menjadi format JSON agar ->change() berhasil
        $articles = DB::table('articles')->get();
        $translatableColumns = ['title', 'slug', 'content', 'excerpt', 'meta_title', 'meta_description'];

        foreach ($articles as $article) {
            $updateData = [];
            foreach ($translatableColumns as $column) {
                if (isset($article->{$column})) {
                    $value = $article->{$column};
                    // Cek apakah data sudah merupakan JSON yang valid
                    json_decode($value);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        // Jika bukan JSON, kita wrap ke dalam struktur JSON untuk locale default (id)
                        $updateData[$column] = json_encode(['id' => $value]);
                    }
                }
            }

            if (!empty($updateData)) {
                DB::table('articles')->where('id', $article->id)->update($updateData);
            }
        }

        // Karena default index database tertentu tidak mendukung unique key utuh pada kolom JSON (seperti MySQL),
        // kita perlu drop constraint unique pada slug terlebih dahulu.
        try {
            Schema::table('articles', function (Blueprint $table) {
                $table->dropUnique(['slug']);
            });
        } catch (\Exception $e) {
            // Constraint tidak ada atau database tidak mendukung
        }

        // Terakhir, kita ->change() semua kolom tersebut ke JSON
        Schema::table('articles', function (Blueprint $table) use ($translatableColumns) {
            foreach ($translatableColumns as $column) {
                $table->json($column)->nullable()->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->string('title')->nullable()->change();
            $table->string('slug')->nullable()->change();
            $table->text('content')->nullable()->change();
            $table->text('excerpt')->nullable()->change();
            $table->string('meta_title')->nullable()->change();
            $table->text('meta_description')->nullable()->change();
        });

        try {
            Schema::table('articles', function (Blueprint $table) {
                $table->unique('slug');
            });
        } catch (\Exception $e) {
            //
        }
    }
};
