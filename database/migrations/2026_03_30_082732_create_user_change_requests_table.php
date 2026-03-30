<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Stores pending Update/Delete requests made by Admin-role users.
     * Super Admin reviews and approves/rejects each request.
     */
    public function up(): void
    {
        Schema::create('user_change_requests', function (Blueprint $table) {
            $table->id();

            // The admin who submitted this change request
            $table->foreignId('requested_by')
                ->constrained('users')
                ->cascadeOnDelete();

            // The target user being edited or deleted
            $table->foreignId('target_user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // 'update' or 'delete'
            $table->string('action');

            // JSON snapshot of the proposed changes (for 'update' action)
            // Keys: name, email, password (hashed), roles (array of role names)
            $table->json('payload')->nullable();

            // 'pending' | 'approved' | 'rejected'
            $table->string('status')->default('pending');

            // Super Admin who reviewed this request
            $table->foreignId('reviewed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('reviewed_at')->nullable();

            // Optional rejection reason
            $table->text('review_note')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_change_requests');
    }
};
