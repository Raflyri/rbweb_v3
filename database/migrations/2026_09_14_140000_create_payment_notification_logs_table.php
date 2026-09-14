<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_notification_logs', function (Blueprint $table) {
            $table->id();

            $table->string('order_id', 64)->nullable()->index();
            $table->string('transaction_id', 64)->nullable()->index();
            $table->string('payment_type', 32)->nullable();
            $table->string('transaction_status', 32)->nullable()->index();
            $table->string('fraud_status', 32)->nullable();
            $table->string('status_code', 16)->nullable();
            $table->decimal('gross_amount', 12, 2)->nullable();
            $table->string('signature_key', 150)->nullable();
            $table->boolean('is_valid_signature')->default(true);
            $table->boolean('is_test_notification')->default(false)->index();
            $table->unsignedSmallInteger('response_status')->default(200);
            $table->string('response_message')->nullable();
            $table->json('payload')->nullable();
            $table->string('ip_address', 45)->nullable();

            $table->timestamps();
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_notification_logs');
    }
};
