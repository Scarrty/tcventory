<?php

declare(strict_types=1);

namespace Tests\Concerns;

use App\Models\Game;
use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\Set;
use App\Models\StorageLocation;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Spatie\Permission\PermissionRegistrar;

trait ApiTestData
{
    protected function seedRolesAndPermissions(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    protected function createUserWithPermissions(array $permissions): User
    {
        $user = User::factory()->create();
        $user->givePermissionTo($permissions);

        return $user;
    }

    protected function createCatalogFixture(): array
    {
        $game = Game::factory()->create();
        $set = Set::factory()->for($game)->create();
        $product = Product::factory()->for($game)->for($set)->create();

        return [$game, $set, $product];
    }

    protected function createInventoryFixture(int $quantity = 5): array
    {
        [, , $product] = $this->createCatalogFixture();
        $sourceLocation = StorageLocation::factory()->create(['name' => 'Source']);
        $targetLocation = StorageLocation::factory()->create(['name' => 'Target']);

        $inventoryItem = InventoryItem::factory()->for($product)->for($sourceLocation)->create([
            'quantity' => $quantity,
            'condition' => 'near_mint',
        ]);

        return [$inventoryItem, $sourceLocation, $targetLocation];
    }

    protected function createInventoryMovementForItem(InventoryItem $inventoryItem, int $quantityDelta = -1, string $movementType = 'adjustment'): InventoryMovement
    {
        return InventoryMovement::query()->create([
            'inventory_item_id' => $inventoryItem->id,
            'movement_type' => $movementType,
            'quantity_delta' => $quantityDelta,
            'from_storage_location_id' => $inventoryItem->storage_location_id,
            'to_storage_location_id' => null,
            'reason' => 'Test movement',
            'occurred_at' => now(),
        ]);
    }

}
