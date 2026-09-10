<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Product;
use App\Support\OrderStatus;
use App\Support\PaymentStatus;
use App\Support\ProductType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        $price = $this->faker->numberBetween(50, 2000) * 1000;
        $qty   = $this->faker->numberBetween(1, 3);

        return [
            // order_number and public_token are assigned by the model itself.
            'product_id'            => Product::factory(),
            'product_name_snapshot' => ucfirst($this->faker->words(3, true)),
            'product_type_snapshot' => ProductType::BARANG,
            'price_snapshot'        => $price,
            'qty'                   => $qty,
            'subtotal'              => $price * $qty,
            'shipping_cost'         => null,
            'total'                 => $price * $qty,
            'customer_name'         => $this->faker->name(),
            'customer_email'        => $this->faker->safeEmail(),
            'customer_phone'        => '08' . $this->faker->numerify('##########'),
            'shipping_address'      => $this->faker->address(),
            'notes'                 => null,
            'status'                => OrderStatus::BARU,
            'payment_status'        => PaymentStatus::MENUNGGU,
        ];
    }

    public function jasa(): static
    {
        return $this->state(fn () => [
            'product_type_snapshot' => ProductType::JASA,
            'shipping_address'      => null,
        ]);
    }

    public function status(string $status): static
    {
        return $this->state(fn () => ['status' => $status]);
    }

    public function paid(): static
    {
        return $this->state(fn () => [
            'payment_status' => PaymentStatus::LUNAS,
            'paid_at'        => now(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn () => ['status' => OrderStatus::DIBATALKAN]);
    }
}
