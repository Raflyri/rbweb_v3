<?php
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

try { 
    Schema::table('articles', function(Blueprint $table) { 
        $table->string('slug_id_virtual')->virtualAs("JSON_UNQUOTE(JSON_EXTRACT(slug, '$.id'))")->nullable(); 
        $table->unique('slug_id_virtual', 'articles_slug_id_virtual_unique'); 
    }); 
    echo "success unique index\n"; 
} catch(\Exception $e) { 
    echo $e->getMessage() . "\n"; 
}

// mark migrations complete
DB::table('migrations')->insertOrIgnore([
    ['migration' => '2026_04_06_063530_optimize_articles_table_performance', 'batch' => 99],
    ['migration' => '2026_04_06_070917_enforce_unique_slug_and_clean_state', 'batch' => 99]
]);
