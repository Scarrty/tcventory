<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Game;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $sealed = fake()->boolean();

        return [
            'game_id' => Game::factory(),
            'set_id' => null,
            'name' => Str::title(fake()->words(2, true)),
            'sku' => fake()->boolean(75) ? fake()->unique()->bothify('SKU-####') : null,
            'product_type' => fake()->randomElement(['single', 'sealed', 'deck']),
            'rarity' => fake()->boolean(50) ? fake()->randomElement(['common', 'uncommon', 'rare', 'mythic']) : null,
            'is_sealed' => $sealed,
        ];
    }
}
