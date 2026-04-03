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
        DB::statement("ALTER TABLE articles MODIFY COLUMN status ENUM('Draft','Pending Review','Scheduled','Published') NOT NULL DEFAULT 'Draft'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert any 'Scheduled' records back to 'Draft' before reverting enum
        DB::table('articles')->where('status', 'Scheduled')->update(['status' => 'Draft']);
        DB::statement("ALTER TABLE articles MODIFY COLUMN status ENUM('Draft','Pending Review','Published') NOT NULL DEFAULT 'Draft'");
    }
};
