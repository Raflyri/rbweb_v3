<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A plain string, not an enum: Blog/Article/News is a starting taxonomy,
     * not a fixed set, and a string lets Filament's Select own the list of
     * valid values without a migration every time it changes.
     */
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->string('type')->default('Article')->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
