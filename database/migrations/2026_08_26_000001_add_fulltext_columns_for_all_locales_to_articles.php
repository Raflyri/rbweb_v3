<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Extend the search columns from en/id to all four supported locales.
     *
     * Malay joins the FULLTEXT index: it is Latin script, so the default
     * parser tokenises it correctly on both MySQL and MariaDB.
     *
     * Japanese deliberately does NOT join the index. The default full-text
     * parser splits on whitespace, which Japanese does not use, so a ja
     * column in this index would simply never match. The fix would be a
     * separate index WITH PARSER ngram, which MariaDB does not implement —
     * and this project's hosting may be either engine. ArticleController
     * therefore falls back to LIKE for ja, where the stored generated column
     * is still cheaper to scan than extracting from the JSON on every row.
     */
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->string('title_ms')->storedAs("JSON_UNQUOTE(JSON_EXTRACT(title, '$.ms'))")->nullable();
            $table->string('title_ja')->storedAs("JSON_UNQUOTE(JSON_EXTRACT(title, '$.ja'))")->nullable();
            $table->text('content_ms')->storedAs("JSON_UNQUOTE(JSON_EXTRACT(content, '$.ms'))")->nullable();
            $table->text('content_ja')->storedAs("JSON_UNQUOTE(JSON_EXTRACT(content, '$.ja'))")->nullable();
        });

        // One index per locale, not one index over every locale.
        // MATCH() requires its column list to match an index EXACTLY, so a
        // combined index can only ever be searched as all six columns at once
        // — which would let an English query rank on Indonesian text. Separate
        // indexes are what make a locale-scoped MATCH possible at all.
        DB::statement('ALTER TABLE articles DROP INDEX articles_fulltext_index');

        foreach (['en', 'id', 'ms'] as $locale) {
            DB::statement(
                "ALTER TABLE articles ADD FULLTEXT INDEX articles_fulltext_{$locale}_index "
                . "(title_{$locale}, content_{$locale})"
            );
        }
    }

    public function down(): void
    {
        foreach (['en', 'id', 'ms'] as $locale) {
            DB::statement("ALTER TABLE articles DROP INDEX articles_fulltext_{$locale}_index");
        }

        Schema::table('articles', function (Blueprint $table) {
            $table->dropColumn(['title_ms', 'title_ja', 'content_ms', 'content_ja']);
        });

        DB::statement(
            'ALTER TABLE articles ADD FULLTEXT INDEX articles_fulltext_index '
            . '(title_en, title_id, content_en, content_id)'
        );
    }
};
