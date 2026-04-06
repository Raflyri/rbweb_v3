<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

$dropIdx = function($name) { try { DB::statement("ALTER TABLE articles DROP INDEX $name"); echo "Dropped index $name\n"; } catch (\Exception $e) {} };
$dropCol = function($name) { try { DB::statement("ALTER TABLE articles DROP COLUMN $name"); echo "Dropped column $name\n"; } catch (\Exception $e) {} };

$dropIdx('articles_status_published_at_index');
$dropIdx('articles_fulltext_index');
$dropCol('title_en');
$dropCol('title_id');
$dropCol('content_en');
$dropCol('content_id');

// Also delete from migrations table
DB::statement("DELETE FROM migrations WHERE migration LIKE '%optimize_articles_table_performance%'");
