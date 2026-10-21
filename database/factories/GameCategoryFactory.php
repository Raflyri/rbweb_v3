<?php

namespace Database\Factories;

use App\Models\GameCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GameCategory>
 */
class GameCategoryFactory extends Factory
{
    protected $model = GameCategory::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->words(2, true);

        return [
            'name' => ucwords($name),
            'slug' => Str::slug($name) . '-' . $this->faker->unique()->numberBetween(100, 9999),
        ];
    }
}
