<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->words(2, true);

        return [
            'name' => ucfirst($name),
            'slug' => Str::slug($name) . '-' . $this->faker->unique()->numberBetween(100, 9999),
            'type' => $this->faker->randomElement(Category::TYPES),
        ];
    }

    public function barang(): static
    {
        return $this->state(fn () => ['type' => Category::TYPE_BARANG]);
    }

    public function jasa(): static
    {
        return $this->state(fn () => ['type' => Category::TYPE_JASA]);
    }
}
