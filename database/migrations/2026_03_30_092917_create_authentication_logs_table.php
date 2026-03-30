<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('authentication_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // 'login' | 'logout'
            $table->string('event', 10);

            // IPv4 or IPv6
            $table->string('ip_address', 45)->nullable();

            $table->text('user_agent')->nullable();

            // Resolved via stevebauman/location (ip-api.com)
            $table->string('country', 100)->nullable();
            $table->string('city', 100)->nullable();

            // Precise event timestamp — stored separately from created_at
            // so it survives any async/queued inserts correctly.
            $table->timestamp('logged_at')->index();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('authentication_logs');
    }
};
