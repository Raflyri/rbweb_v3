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
        Schema::table('articles', function (Blueprint $table) {
            // Composite index for fast published_at sorting
            $table->index(['status', 'published_at'], 'articles_status_published_at_index');
            
            // Generated columns for searching
            $table->string('title_en')->virtualAs("JSON_UNQUOTE(JSON_EXTRACT(title, '$.en'))")->nullable();
            $table->string('title_id')->virtualAs("JSON_UNQUOTE(JSON_EXTRACT(title, '$.id'))")->nullable();
            $table->text('content_en')->virtualAs("JSON_UNQUOTE(JSON_EXTRACT(content, '$.en'))")->nullable();
            $table->text('content_id')->virtualAs("JSON_UNQUOTE(JSON_EXTRACT(content, '$.id'))")->nullable();
        });

        // Add FULLTEXT index via raw statement
        DB::statement('ALTER TABLE articles ADD FULLTEXT INDEX articles_fulltext_index (title_en, title_id, content_en, content_id)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropIndex('articles_fulltext_index');
            $table->dropIndex('articles_status_published_at_index');
            $table->dropColumn(['title_en', 'title_id', 'content_en', 'content_id']);
        });
    }
};
