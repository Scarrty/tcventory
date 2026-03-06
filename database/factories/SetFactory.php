<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Game;
use App\Models\Set;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Set>
 */
class SetFactory extends Factory
{
    protected $model = Set::class;

    public function definition(): array
    {
        return [
            'game_id' => Game::factory(),
            'name' => Str::title(fake()->words(3, true)),
            'code' => Str::upper(fake()->bothify('??##')),
            'release_date' => fake()->optional()->date(),
        ];
    }
}
