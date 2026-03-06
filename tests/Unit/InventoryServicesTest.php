<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Game;
use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\StorageLocation;
use App\Services\Inventory\AdjustInventoryStockService;
use App\Services\Inventory\TransferInventoryItemService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class InventoryServicesTest extends TestCase
{
    use RefreshDatabase;

    public function test_adjust_inventory_stock_service_prevents_negative_stock(): void
    {
        $item = $this->createInventoryItem(quantity: 2);

        $this->expectException(ValidationException::class);

        app(AdjustInventoryStockService::class)->execute($item, -3, 'damage');
    }

    public function test_transfer_inventory_item_service_rejects_invalid_target_location(): void
    {
        $item = $this->createInventoryItem(quantity: 3);

        $this->expectException(ValidationException::class);

        app(TransferInventoryItemService::class)->execute($item, 1, 999_999, 'move');
    }

    public function test_transfer_inventory_item_service_is_idempotent_for_request_key(): void
    {
        $item = $this->createInventoryItem(quantity: 5);
        $targetLocation = StorageLocation::query()->create(['name' => 'Target', 'type' => 'shelf']);

        $service = app(TransferInventoryItemService::class);

        $service->execute($item, 2, $targetLocation->id, 'move', 'transfer-key-1');
        $service->execute($item->fresh(), 2, $targetLocation->id, 'move', 'transfer-key-1');

        $this->assertSame(3, $item->fresh()->quantity);
        $this->assertSame(1, InventoryMovement::query()->where('movement_type', 'transfer')->count());
    }

    private function createInventoryItem(int $quantity): InventoryItem
    {
        $game = Game::query()->create(['name' => 'Pokemon', 'slug' => 'pokemon']);
        $product = Product::query()->create([
            'game_id' => $game->id,
            'name' => 'Card',
            'product_type' => 'single',
        ]);
        $location = StorageLocation::query()->create(['name' => 'Main', 'type' => 'shelf']);

        return InventoryItem::query()->create([
            'product_id' => $product->id,
            'storage_location_id' => $location->id,
            'quantity' => $quantity,
            'condition' => 'near_mint',
        ]);
    }
}
