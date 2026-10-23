<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('paylabs_transaction_id', 64)->nullable()->after('midtrans_payment_payload');
            $table->string('paylabs_payment_type', 32)->nullable()->after('paylabs_transaction_id');
            $table->json('paylabs_payment_payload')->nullable()->after('paylabs_payment_type');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'paylabs_transaction_id',
                'paylabs_payment_type',
                'paylabs_payment_payload',
            ]);
        });
    }
};
