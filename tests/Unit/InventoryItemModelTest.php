<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Game;
use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\StorageLocation;
use App\Models\Valuation;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryItemModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_inventory_item_has_expected_casts(): void
    {
        $casts = (new InventoryItem())->getCasts();

        $this->assertSame('datetime', $casts['acquired_at']);
        $this->assertSame('integer', $casts['quantity']);
    }

    public function test_inventory_item_relations_are_configured_correctly(): void
    {
        $inventoryItem = new InventoryItem();

        $this->assertInstanceOf(BelongsTo::class, $inventoryItem->product());
        $this->assertInstanceOf(BelongsTo::class, $inventoryItem->storageLocation());
        $this->assertInstanceOf(HasMany::class, $inventoryItem->inventoryMovements());
        $this->assertInstanceOf(HasMany::class, $inventoryItem->valuations());
    }

    public function test_product_and_storage_location_have_inventory_items_relations(): void
    {
        $this->assertInstanceOf(HasMany::class, (new Product())->inventoryItems());
        $this->assertInstanceOf(HasMany::class, (new StorageLocation())->inventoryItems());
    }

    public function test_inventory_item_belongs_to_product_and_storage_location(): void
    {
        $game = Game::query()->create([
            'name' => 'Pokemon TCG',
            'slug' => 'pokemon-tcg',
        ]);

        $product = Product::query()->create([
            'game_id' => $game->id,
            'name' => 'Booster Pack',
            'product_type' => 'sealed',
        ]);

        $storageLocation = StorageLocation::query()->create([
            'name' => 'Shelf A',
            'type' => 'shelf',
        ]);

        $inventoryItem = InventoryItem::query()->create([
            'product_id' => $product->id,
            'storage_location_id' => $storageLocation->id,
            'quantity' => 3,
            'condition' => 'near_mint',
        ]);

        $this->assertTrue($inventoryItem->product->is($product));
        $this->assertTrue($inventoryItem->storageLocation->is($storageLocation));
    }

    public function test_inventory_item_has_many_inventory_movements_and_valuations(): void
    {
        $game = Game::query()->create([
            'name' => 'Magic The Gathering',
            'slug' => 'mtg',
        ]);

        $product = Product::query()->create([
            'game_id' => $game->id,
            'name' => 'Black Lotus',
            'product_type' => 'single',
        ]);

        $inventoryItem = InventoryItem::query()->create([
            'product_id' => $product->id,
            'quantity' => 1,
            'condition' => 'mint',
        ]);

        InventoryMovement::query()->create([
            'inventory_item_id' => $inventoryItem->id,
            'movement_type' => 'adjustment',
            'quantity_delta' => 1,
            'occurred_at' => now(),
        ]);

        Valuation::query()->create([
            'inventory_item_id' => $inventoryItem->id,
            'value_amount' => 99.99,
            'currency' => 'EUR',
            'source' => 'manual',
            'valued_at' => now(),
        ]);

        $this->assertCount(1, $inventoryItem->inventoryMovements);
        $this->assertCount(1, $inventoryItem->valuations);
    }
}
