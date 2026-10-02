<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('launchpad_links', function (Blueprint $table) {
            $table->boolean('is_monitored')->default(false)->after('is_active');
            $table->string('monitoring_status')->nullable()->after('is_monitored');
            $table->timestamp('last_checked_at')->nullable()->after('monitoring_status');
            $table->integer('http_response_time')->nullable()->after('last_checked_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('launchpad_links', function (Blueprint $table) {
            $table->dropColumn([
                'is_monitored',
                'monitoring_status',
                'last_checked_at',
                'http_response_time',
            ]);
        });
    }
};
