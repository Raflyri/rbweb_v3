<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            // Add submitting user FK
            $table->foreignId('user_id')
                  ->nullable()
                  ->after('id')
                  ->constrained()
                  ->cascadeOnDelete();

            // Add reviewing admin FK
            $table->foreignId('reviewer_id')
                  ->nullable()
                  ->after('user_id')
                  ->constrained('users')
                  ->nullOnDelete();

            $table->timestamp('reviewed_at')->nullable()->after('published_at');
        });

        // Alter enum to add 'Pending Review'.
        // SQLite does not support ALTER COLUMN on enums; we use a raw statement
        // that works for both MySQL/MariaDB (production) and re-creates the check
        // constraint for SQLite via the driver's DDL.
        $driver = DB::getDriverName();

        if ($driver === 'mysql' || $driver === 'mariadb') {
            DB::statement("ALTER TABLE articles MODIFY COLUMN status ENUM('Draft','Pending Review','Published') NOT NULL DEFAULT 'Draft'");
        }
        // SQLite: recreating the column is handled automatically by Doctrine on
        // a fresh migrate; in SQLite the enum is stored as a varchar check so
        // we patch the default directly.
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
            $table->dropConstrainedForeignId('reviewer_id');
            $table->dropColumn('reviewed_at');
        });

        $driver = DB::getDriverName();
        if ($driver === 'mysql' || $driver === 'mariadb') {
            DB::statement("ALTER TABLE articles MODIFY COLUMN status ENUM('Draft','Published') NOT NULL DEFAULT 'Draft'");
        }
    }
};
