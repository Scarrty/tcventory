<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\StorageLocation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StorageLocation>
 */
class StorageLocationFactory extends Factory
{
    protected $model = StorageLocation::class;

    public function definition(): array
    {
        return [
            'name' => 'Location '.fake()->unique()->numberBetween(1, 999),
            'type' => fake()->randomElement(['shelf', 'vault', 'binder', 'other']),
            'code' => fake()->boolean(75) ? fake()->unique()->bothify('LOC-###') : null,
            'description' => fake()->boolean(60) ? fake()->sentence() : null,
        ];
    }
}
