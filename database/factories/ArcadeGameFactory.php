<?php

namespace Database\Factories;

use App\Models\ArcadeGame;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ArcadeGame>
 */
class ArcadeGameFactory extends Factory
{
    protected $model = ArcadeGame::class;

    public function definition(): array
    {
        $title = $this->faker->unique()->words(3, true);

        return [
            'title'       => ucwords($title),
            'slug'        => Str::slug($title) . '-' . $this->faker->unique()->numberBetween(100, 9999),
            'description' => $this->faker->paragraph(),
            'thumbnail'   => null,
            'game_url'    => 'https://arcade-game-demo.pages.dev',
            'status'      => ArcadeGame::STATUS_TAYANGKAN,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn () => ['status' => ArcadeGame::STATUS_DRAFT]);
    }

    public function published(): static
    {
        return $this->state(fn () => ['status' => ArcadeGame::STATUS_TAYANGKAN]);
    }

    public function spotlight(): static
    {
        return $this->state(fn () => [
            'status'       => ArcadeGame::STATUS_TAYANGKAN,
            'is_spotlight' => true,
        ]);
    }
}
