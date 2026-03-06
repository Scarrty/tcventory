<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\InventoryItem;
use App\Models\Product;
use App\Models\StorageLocation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InventoryItem>
 */
class InventoryItemFactory extends Factory
{
    protected $model = InventoryItem::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'storage_location_id' => StorageLocation::factory(),
            'quantity' => fake()->numberBetween(1, 10),
            'condition' => fake()->randomElement(['mint', 'near_mint', 'light_play', 'sealed']),
            'grading_provider' => null,
            'grade' => null,
            'acquired_at' => fake()->optional()->dateTime(),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
