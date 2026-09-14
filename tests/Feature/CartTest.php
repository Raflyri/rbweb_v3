<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Services\Cart\CartService;
use App\Support\ProductType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    public function test_empty_cart_page_renders(): void
    {
        $response = $this->get(route('cart.index'));

        $response->assertOk();
        $response->assertSee('Keranjang Anda Masih Kosong');
    }

    public function test_can_add_product_to_cart(): void
    {
        $product = Product::factory()->create([
            'is_active' => true,
            'price'     => 150000,
            'type'      => ProductType::BARANG,
            'stock'     => 10,
        ]);

        $response = $this->post(route('cart.add', $product->slug), [
            'qty' => 2,
        ]);

        $response->assertRedirect(route('cart.index'));
        $this->followRedirects($response)->assertSee('Produk berhasil ditambahkan');

        $cart = app(CartService::class);
        $this->assertEquals(2, $cart->count());
        $this->assertEquals(300000, $cart->subtotal());
    }

    public function test_can_update_product_qty_in_cart(): void
    {
        $product = Product::factory()->create([
            'is_active' => true,
            'price'     => 50000,
            'type'      => ProductType::BARANG,
            'stock'     => 10,
        ]);

        $cart = app(CartService::class);
        $cart->add($product, 1);

        $response = $this->post(route('cart.update'), [
            'product_id' => $product->id,
            'qty'        => 3,
        ]);

        $response->assertRedirect(route('cart.index'));
        $this->assertEquals(3, $cart->count());
        $this->assertEquals(150000, $cart->subtotal());
    }

    public function test_can_remove_product_from_cart(): void
    {
        $product = Product::factory()->create([
            'is_active' => true,
            'price'     => 50000,
            'type'      => ProductType::BARANG,
        ]);

        $cart = app(CartService::class);
        $cart->add($product, 1);
        $this->assertEquals(1, $cart->count());

        $response = $this->delete(route('cart.remove', $product->id));

        $response->assertRedirect(route('cart.index'));
        $this->assertTrue($cart->isEmpty());
    }

    public function test_can_clear_cart(): void
    {
        $p1 = Product::factory()->create(['is_active' => true, 'price' => 10000]);
        $p2 = Product::factory()->create(['is_active' => true, 'price' => 20000]);

        $cart = app(CartService::class);
        $cart->add($p1, 1);
        $cart->add($p2, 2);
        $this->assertEquals(3, $cart->count());

        $response = $this->post(route('cart.clear'));

        $response->assertRedirect(route('cart.index'));
        $this->assertTrue($cart->isEmpty());
    }

    public function test_cart_honours_stock_limit(): void
    {
        $product = Product::factory()->create([
            'is_active' => true,
            'price'     => 10000,
            'stock'     => 5,
        ]);

        $cart = app(CartService::class);
        $cart->add($product, 10);

        $this->assertEquals(5, $cart->count());
    }
}
