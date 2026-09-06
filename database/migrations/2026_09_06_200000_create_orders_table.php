<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            // Human-facing reference: RB-20260906-0001. Sequential by design —
            // it is what buyer and admin say to each other on WhatsApp.
            $table->string('order_number', 32)->unique();

            // What the public URL actually uses. The order number is guessable
            // by construction, and /pesanan/{...} shows a name, a phone number
            // and a shipping address — so the page is keyed by an unguessable
            // random token instead, and the readable number stays for humans.
            $table->string('public_token', 64)->unique();

            // Keep order history when a product is deleted; the snapshot columns
            // below are what the order actually renders from.
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();

            // Frozen at order time. Editing a product's name or price later must
            // not rewrite what someone already ordered and agreed to pay.
            $table->string('product_name_snapshot');
            $table->string('product_type_snapshot', 20);
            $table->decimal('price_snapshot', 12, 2)->nullable();

            $table->unsignedInteger('qty')->default(1);
            $table->decimal('subtotal', 12, 2);

            // Shipping is quoted by hand over WhatsApp for now, so the admin
            // needs somewhere to record it — and a total that reflects it —
            // without a follow-up migration once phase 4 takes payments.
            $table->decimal('shipping_cost', 12, 2)->nullable();
            $table->decimal('total', 12, 2)->nullable();

            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone', 40);

            // Goods only. A service has nothing to ship.
            $table->text('shipping_address')->nullable();

            // Services only: when the buyer would like it done.
            $table->date('preferred_date')->nullable();

            $table->text('notes')->nullable();

            // Strings, not ENUMs — see App\Support\OrderStatus / PaymentStatus.
            $table->string('status', 20)->default('baru')->index();
            $table->string('payment_status', 24)->default('menunggu')->index();

            // Filled from phase 4 onwards; the columns exist now so the payment
            // work is a code change rather than another schema change.
            $table->string('payment_method', 40)->nullable();
            $table->string('payment_proof')->nullable();
            $table->text('payment_note')->nullable();
            $table->timestamp('paid_at')->nullable();

            $table->timestamps();

            // The admin list is "newest first, optionally filtered by status".
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
