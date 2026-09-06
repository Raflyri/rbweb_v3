<?php

namespace Database\Factories;

use App\Models\Product;
use App\Support\ProductType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $name = ucfirst($this->faker->unique()->words(3, true));

        return [
            // slug is intentionally omitted: HasSlug generates it on create,
            // which is exactly the behaviour the tests need to exercise.
            'name'              => ['id' => $name, 'en' => $name],
            'short_description' => [
                'id' => $this->faker->sentence(12),
                'en' => $this->faker->sentence(12),
            ],
            'description'       => [
                'id' => '<p>' . $this->faker->paragraph(4) . '</p>',
                'en' => '<p>' . $this->faker->paragraph(4) . '</p>',
            ],
            'type'              => ProductType::BARANG,
            'price'             => $this->faker->numberBetween(50, 5000) * 1000,
            'currency'          => 'IDR',
            'stock'             => null,
            'thumbnail'         => null,
            'is_active'         => true,
            'is_featured'       => false,
            'sort_order'        => 0,
        ];
    }

    public function barang(): static
    {
        return $this->state(fn () => ['type' => ProductType::BARANG]);
    }

    public function jasa(): static
    {
        return $this->state(fn () => [
            'type'  => ProductType::JASA,
            // A service has no shelf to run out of.
            'stock' => null,
        ]);
    }

    /** No price — the public page shows "Hubungi Kami" instead of a number. */
    public function withoutPrice(): static
    {
        return $this->state(fn () => ['price' => null]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }

    public function featured(): static
    {
        return $this->state(fn () => ['is_featured' => true]);
    }

    /** Tracked stock that has run out. */
    public function outOfStock(): static
    {
        return $this->state(fn () => [
            'type'  => ProductType::BARANG,
            'stock' => 0,
        ]);
    }

    /** Indonesian copy only — the common real-world case. */
    public function indonesianOnly(): static
    {
        return $this->state(function (array $attributes) {
            $name = is_array($attributes['name'] ?? null)
                ? ($attributes['name']['id'] ?? 'Produk')
                : 'Produk';

            return [
                'name'              => ['id' => $name],
                'short_description' => ['id' => $this->faker->sentence(12)],
                'description'       => ['id' => '<p>' . $this->faker->paragraph(3) . '</p>'],
            ];
        });
    }
}
