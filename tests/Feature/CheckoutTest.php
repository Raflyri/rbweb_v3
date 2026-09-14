<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Services\Cart\CartService;
use App\Services\Payment\ManualTransferGateway;
use App\Support\OrderStatus;
use App\Support\PaymentStatus;
use App\Support\ProductType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_empty_cart_redirects_from_checkout(): void
    {
        $response = $this->get(route('checkout.index'));

        $response->assertRedirect(route('cart.index'));
    }

    public function test_checkout_page_displays_items_and_payment_methods(): void
    {
        $product = Product::factory()->create([
            'is_active' => true,
            'price'     => 120000,
            'type'      => ProductType::BARANG,
        ]);

        $cart = app(CartService::class);
        $cart->add($product, 2);

        $response = $this->get(route('checkout.index'));

        $response->assertOk();
        $response->assertSee('Checkout Pembayaran');
        $response->assertSee($product->translate('name', 'id') ?: $product->slug);
        $response->assertSee('Transfer Bank Manual');
    }

    public function test_can_process_checkout_with_manual_transfer(): void
    {
        $p1 = Product::factory()->create([
            'is_active' => true,
            'price'     => 100000,
            'type'      => ProductType::BARANG,
        ]);
        $p2 = Product::factory()->create([
            'is_active' => true,
            'price'     => 50000,
            'type'      => ProductType::BARANG,
        ]);

        $cart = app(CartService::class);
        $cart->add($p1, 1);
        $cart->add($p2, 2);

        $this->assertEquals(3, $cart->count());
        $this->assertEquals(200000, $cart->subtotal());

        $response = $this->post(route('checkout.store'), [
            'customer_name'    => 'John Doe',
            'customer_email'   => 'john@example.com',
            'customer_phone'   => '081234567890',
            'shipping_address' => 'Jl. Merdeka No. 45, Jakarta',
            'notes'            => 'Tolong kirim bubble wrap tebal',
            'payment_method'   => ManualTransferGateway::KEY,
        ]);

        $order = Order::latest('id')->first();
        $this->assertNotNull($order);
        $response->assertRedirect(route('order.pending', $order->public_token));

        $this->assertEquals(OrderStatus::BARU, $order->status);
        $this->assertEquals(PaymentStatus::MENUNGGU, $order->payment_status);
        $this->assertEquals(ManualTransferGateway::KEY, $order->payment_method);
        $this->assertEquals(200000, (float) $order->subtotal);
        $this->assertEquals(3, $order->qty);

        // Assert items table
        $this->assertEquals(2, $order->items()->count());
        $this->assertDatabaseHas('order_items', [
            'order_id'   => $order->id,
            'product_id' => $p1->id,
            'qty'        => 1,
            'subtotal'   => 100000,
        ]);
        $this->assertDatabaseHas('order_items', [
            'order_id'   => $order->id,
            'product_id' => $p2->id,
            'qty'        => 2,
            'subtotal'   => 100000,
        ]);

        // Cart is cleared after checkout
        $this->assertTrue($cart->isEmpty());
    }

    public function test_single_product_order_form_persists_order_items(): void
    {
        $product = Product::factory()->create([
            'is_active' => true,
            'price'     => 75000,
            'type'      => ProductType::BARANG,
        ]);

        $response = $this->post(route('order.store', $product->slug), [
            'customer_name'    => 'Jane Doe',
            'customer_email'   => 'jane@example.com',
            'customer_phone'   => '08987654321',
            'qty'              => 2,
            'shipping_address' => 'Jl. Sudirman No. 10',
            'payment_method'   => ManualTransferGateway::KEY,
        ]);

        $order = Order::latest('id')->first();
        $this->assertNotNull($order);
        $response->assertRedirect(route('order.pending', $order->public_token));

        $this->assertEquals(1, $order->items()->count());
        $this->assertDatabaseHas('order_items', [
            'order_id'   => $order->id,
            'product_id' => $product->id,
            'qty'        => 2,
            'subtotal'   => 150000,
        ]);
    }
}
