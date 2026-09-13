<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('midtrans_transaction_id', 64)->nullable()->after('payment_note');
            $table->string('midtrans_payment_type', 32)->nullable()->after('midtrans_transaction_id');
            $table->json('midtrans_payment_payload')->nullable()->after('midtrans_payment_type');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'midtrans_transaction_id',
                'midtrans_payment_type',
                'midtrans_payment_payload',
            ]);
        });
    }
};
