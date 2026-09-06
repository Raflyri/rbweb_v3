<?php

use App\Models\Order;
use App\Models\Product;
use App\Notifications\NewOrderReceived;
use App\Support\OrderStatus;
use App\Support\PaymentStatus;
use Illuminate\Support\Facades\Notification;

use function Pest\Laravel\get;
use function Pest\Laravel\post;

/** A minimal, valid submission for a physical product. */
function orderPayload(array $overrides = []): array
{
    return array_merge([
        'customer_name'    => 'Rafly Rizky',
        'customer_email'   => 'pembeli@example.com',
        'customer_phone'   => '0812 3456 7890',
        'qty'              => 2,
        'shipping_address' => 'Jl. Merdeka No. 10, Bandung, 40111',
        'notes'            => null,
    ], $overrides);
}

/*
|--------------------------------------------------------------------------
| Reaching the form
|--------------------------------------------------------------------------
*/

it('opens the order form for a product that can be bought', function () {
    $product = Product::factory()->create([
        'name'  => ['id' => 'Kabel LAN Cat6'],
        'price' => 125000,
    ]);

    get(route('order.create', $product->slug))
        ->assertOk()
        ->assertSee('Kabel LAN Cat6')
        ->assertSee('Alamat pengiriman');
});

it('asks for a schedule instead of an address when ordering a service', function () {
    $product = Product::factory()->jasa()->create(['price' => 750000]);

    get(route('order.create', $product->slug))
        ->assertOk()
        ->assertSee('Tanggal yang diinginkan')
        ->assertDontSee('Alamat pengiriman');
});

it('sends a quote-only product back to its page instead of showing a form', function () {
    $product = Product::factory()->withoutPrice()->create();

    get(route('order.create', $product->slug))
        ->assertRedirect(route('products.show', $product->slug))
        ->assertSessionHas('order_error');

    expect(Order::count())->toBe(0);
});

it('refuses to take an order for something out of stock', function () {
    $product = Product::factory()->outOfStock()->create(['price' => 125000]);

    get(route('order.create', $product->slug))
        ->assertRedirect(route('products.show', $product->slug))
        ->assertSessionHas('order_error');
});

it('404s the order form for a hidden product', function () {
    $product = Product::factory()->inactive()->create(['price' => 125000]);

    get(route('order.create', $product->slug))->assertNotFound();
});

/*
|--------------------------------------------------------------------------
| Placing an order
|--------------------------------------------------------------------------
*/

it('records an order and sends the buyer to their own page', function () {
    Notification::fake();

    $product = Product::factory()->create([
        'name'  => ['id' => 'Kabel LAN Cat6'],
        'price' => 125000,
    ]);

    $response = post(route('order.store', $product->slug), orderPayload());

    $order = Order::firstOrFail();

    $response->assertRedirect(route('order.pending', $order->public_token));

    expect($order->product_id)->toBe($product->id)
        ->and($order->product_name_snapshot)->toBe('Kabel LAN Cat6')
        ->and($order->qty)->toBe(2)
        ->and((float) $order->subtotal)->toBe(250000.0)
        ->and((float) $order->total)->toBe(250000.0)
        ->and($order->shipping_cost)->toBeNull()
        ->and($order->status)->toBe(OrderStatus::BARU)
        ->and($order->payment_status)->toBe(PaymentStatus::MENUNGGU)
        ->and($order->customer_email)->toBe('pembeli@example.com');
});

it('prices the order from the product, not from the submitted form', function () {
    $product = Product::factory()->create(['price' => 125000]);

    // A hidden price input is a price the buyer can edit — the server must
    // ignore anything they send for it.
    post(route('order.store', $product->slug), orderPayload([
        'qty'            => 1,
        'price_snapshot' => 1,
        'subtotal'       => 1,
        'total'          => 1,
    ]));

    expect((float) Order::firstOrFail()->subtotal)->toBe(125000.0);
});

it('generates a dated, sequential order number', function () {
    $product = Product::factory()->create(['price' => 125000]);

    post(route('order.store', $product->slug), orderPayload());
    post(route('order.store', $product->slug), orderPayload());

    $numbers = Order::orderBy('id')->pluck('order_number')->all();
    $today   = now()->format('Ymd');

    expect($numbers[0])->toBe("RB-{$today}-0001")
        ->and($numbers[1])->toBe("RB-{$today}-0002");
});

it('gives every order an unguessable public token', function () {
    $product = Product::factory()->create(['price' => 125000]);

    post(route('order.store', $product->slug), orderPayload());
    post(route('order.store', $product->slug), orderPayload());

    $tokens = Order::pluck('public_token');

    expect($tokens[0])->not->toBe($tokens[1])
        ->and(strlen($tokens[0]))->toBeGreaterThanOrEqual(32)
        // The readable number must not be derivable from the URL key.
        ->and($tokens[0])->not->toContain('RB-');
});

it('emails the shop about the new order', function () {
    Notification::fake();

    $product = Product::factory()->create(['price' => 125000]);

    post(route('order.store', $product->slug), orderPayload());

    Notification::assertSentOnDemand(NewOrderReceived::class);
});

it('still records the order when the mail server fails', function () {
    // The buyer's order is the thing that matters; a broken SMTP host must not
    // turn a successful submission into a 500.
    Notification::shouldReceive('route')->andThrow(new RuntimeException('smtp down'));

    $product = Product::factory()->create(['price' => 125000]);

    post(route('order.store', $product->slug), orderPayload())->assertRedirect();

    expect(Order::count())->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Validation
|--------------------------------------------------------------------------
*/

it('requires a shipping address for physical goods', function () {
    $product = Product::factory()->barang()->create(['price' => 125000]);

    post(route('order.store', $product->slug), orderPayload(['shipping_address' => null]))
        ->assertSessionHasErrors('shipping_address');

    expect(Order::count())->toBe(0);
});

it('does not require an address for a service', function () {
    $product = Product::factory()->jasa()->create(['price' => 750000]);

    post(route('order.store', $product->slug), orderPayload([
        'shipping_address' => null,
        'preferred_date'   => now()->addWeek()->toDateString(),
    ]))->assertSessionHasNoErrors();

    $order = Order::firstOrFail();

    expect($order->shipping_address)->toBeNull()
        ->and($order->preferred_date->toDateString())->toBe(now()->addWeek()->toDateString());
});

it('rejects a schedule in the past', function () {
    $product = Product::factory()->jasa()->create(['price' => 750000]);

    post(route('order.store', $product->slug), orderPayload([
        'shipping_address' => null,
        'preferred_date'   => now()->subDay()->toDateString(),
    ]))->assertSessionHasErrors('preferred_date');
});

it('requires the contact details we would need to fulfil the order', function () {
    $product = Product::factory()->create(['price' => 125000]);

    post(route('order.store', $product->slug), orderPayload([
        'customer_name'  => '',
        'customer_email' => 'bukan-email',
        'customer_phone' => '',
    ]))->assertSessionHasErrors(['customer_name', 'customer_email', 'customer_phone']);
});

it('refuses an order for more than the stock on hand', function () {
    $product = Product::factory()->barang()->create(['price' => 125000, 'stock' => 3]);

    post(route('order.store', $product->slug), orderPayload(['qty' => 5]))
        ->assertSessionHasErrors('qty');

    expect(Order::count())->toBe(0);
});

it('drops a submission that filled in the honeypot', function () {
    $product = Product::factory()->create(['price' => 125000]);

    post(route('order.store', $product->slug), orderPayload(['website' => 'http://spam.example']))
        ->assertSessionHasErrors('website');

    expect(Order::count())->toBe(0);
});

/*
|--------------------------------------------------------------------------
| The buyer's order page
|--------------------------------------------------------------------------
*/

it('shows the order to whoever holds the link', function () {
    $order = Order::factory()->create([
        'customer_name' => 'Rafly Rizky',
        'subtotal'      => 250000,
        'total'         => 250000,
    ]);

    get(route('order.pending', $order->public_token))
        ->assertOk()
        ->assertSee($order->order_number)
        ->assertSee('Rafly Rizky')
        ->assertSee('Rp 250.000')
        // Nobody should be able to find this page in a search engine.
        ->assertSee('noindex, nofollow', false);
});

it('404s an order token that does not exist', function () {
    get(route('order.pending', 'token-yang-tidak-ada'))->assertNotFound();
});

it('says the total is provisional until shipping has been quoted', function () {
    $order = Order::factory()->create(['shipping_cost' => null]);

    get(route('order.pending', $order->public_token))
        ->assertOk()
        ->assertSee('Belum dihitung');
});
