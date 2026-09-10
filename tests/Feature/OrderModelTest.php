<?php

use App\Models\Order;
use App\Models\Product;
use App\Support\OrderStatus;
use App\Support\PaymentStatus;
use App\Support\ProductType;

it('numbers orders per day, restarting the sequence each day', function () {
    $today = now()->format('Ymd');

    Order::factory()->create();

    expect(Order::first()->order_number)->toBe("RB-{$today}-0001");

    // A different day starts over at 0001 rather than continuing forever.
    $yesterday = now()->subDay();

    expect(Order::generateOrderNumber($yesterday))
        ->toBe('RB-' . $yesterday->format('Ymd') . '-0001');
});

it('does not reuse the number of a deleted order', function () {
    $first = Order::factory()->create();
    Order::factory()->create();

    $first->delete();

    $next = Order::place(Order::factory()->raw());

    // Counting rows would have handed this the number just freed up, so two
    // different orders would share a reference.
    expect($next->order_number)->toBe('RB-' . now()->format('Ymd') . '-0003');
});

it('keeps the product name it was ordered under when the product is renamed', function () {
    $product = Product::factory()->create(['name' => ['id' => 'Nama Saat Dipesan'], 'price' => 100000]);

    $order = Order::factory()->create([
        'product_id'            => $product->id,
        'product_name_snapshot' => $product->translate('name', 'id'),
    ]);

    $product->update(['name' => ['id' => 'Nama Baru Setelah Rebranding'], 'price' => 999000]);

    expect($order->fresh()->product_name_snapshot)->toBe('Nama Saat Dipesan');
});

it('survives the product being deleted', function () {
    $product = Product::factory()->create(['price' => 100000]);
    $order   = Order::factory()->create(['product_id' => $product->id]);

    $product->delete();

    $order = $order->fresh();

    // History is the point: the order still reads correctly with nothing to
    // link to any more.
    expect($order)->not->toBeNull()
        ->and($order->product_id)->toBeNull()
        ->and($order->product_name_snapshot)->not->toBeEmpty();
});

it('falls back to the subtotal until shipping has been quoted', function () {
    $order = Order::factory()->create([
        'subtotal'      => 250000,
        'shipping_cost' => null,
        'total'         => 250000,
    ]);

    expect($order->formattedTotal())->toBe('Rp 250.000')
        ->and($order->formattedShipping())->toBeNull();

    $order->shipping_cost = 25000;
    $order->recalculateTotal();

    expect((float) $order->total)->toBe(275000.0)
        ->and($order->formattedTotal())->toBe('Rp 275.000')
        ->and($order->formattedShipping())->toBe('Rp 25.000');
});

it('knows which orders still need shipping arranged', function () {
    $goods   = Order::factory()->create();
    $service = Order::factory()->jasa()->create();

    expect($goods->needsShipping())->toBeTrue()
        ->and($service->needsShipping())->toBeFalse()
        ->and($service->product_type_snapshot)->toBe(ProductType::JASA);
});

it('filters orders by where they stand', function () {
    $fresh     = Order::factory()->create();
    $done      = Order::factory()->status(OrderStatus::SELESAI)->paid()->create();
    $cancelled = Order::factory()->cancelled()->create();

    expect(Order::baru()->pluck('id')->all())->toBe([$fresh->id])
        ->and(Order::paid()->pluck('id')->all())->toBe([$done->id])
        ->and(Order::awaitingPayment()->pluck('id')->all())
            ->toContain($fresh->id, $cancelled->id)
            ->not->toContain($done->id);
});

it('starts every order unpaid and untouched', function () {
    $order = Order::factory()->create();

    expect($order->status)->toBe(OrderStatus::BARU)
        ->and($order->payment_status)->toBe(PaymentStatus::MENUNGGU)
        ->and($order->paid_at)->toBeNull()
        ->and($order->isPaid())->toBeFalse();
});
