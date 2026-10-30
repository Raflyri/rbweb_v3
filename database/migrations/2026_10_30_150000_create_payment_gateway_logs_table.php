<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_gateway_logs', function (Blueprint $table) {
            $table->id();

            $table->string('order_id', 191)->nullable()->index();
            $table->string('gateway', 32)->default('paylabs')->index(); // 'paylabs', 'midtrans'
            $table->string('direction', 16)->default('outgoing')->index(); // 'outgoing' (to gateway) or 'incoming' (from gateway)
            $table->string('action', 64)->nullable()->index(); // 'create_qris', 'create_va', 'query_status', 'cancel_qris', 'webhook_notification'
            $table->string('method', 10)->default('POST'); // 'POST', 'GET'
            $table->string('endpoint', 255)->nullable();
            $table->string('transaction_id', 191)->nullable()->index();
            $table->decimal('amount', 14, 2)->nullable();
            $table->unsignedSmallInteger('response_status')->nullable();
            $table->string('status', 32)->default('pending')->index(); // 'success', 'failed', 'invalid_signature', 'pending'
            $table->text('error_message')->nullable();
            $table->json('request_headers')->nullable();
            $table->json('request_payload')->nullable();
            $table->json('response_headers')->nullable();
            $table->json('response_payload')->nullable();
            $table->string('ip_address', 45)->nullable();

            $table->timestamps();
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_gateway_logs');
    }
};
