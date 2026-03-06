<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\InventoryMovement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\ApiTestData;
use Tests\TestCase;

class InventoryItemApiTest extends TestCase
{
    use ApiTestData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRolesAndPermissions();
    }

    public function test_index_store_show_and_update_work_for_authorized_user(): void
    {
        $user = $this->createUserWithPermissions(['inventory.view', 'inventory.create', 'inventory.update']);
        Sanctum::actingAs($user);

        [$inventoryItem] = $this->createInventoryFixture(quantity: 3);

        $this->getJson('/api/v1/inventory-items')->assertOk()->assertJsonPath('data.0.id', $inventoryItem->id);

        $created = $this->postJson('/api/v1/inventory-items', [
            'product_id' => $inventoryItem->product_id,
            'storage_location_id' => $inventoryItem->storage_location_id,
            'quantity' => 2,
            'condition' => 'mint',
        ])->assertCreated();

        $id = $created->json('data.id');

        $this->getJson("/api/v1/inventory-items/{$id}")->assertOk()->assertJsonPath('data.quantity', 2);

        $this->patchJson("/api/v1/inventory-items/{$id}", ['quantity' => 5])
            ->assertOk()
            ->assertJsonPath('data.quantity', 5);
    }

    public function test_store_and_update_validate_payloads(): void
    {
        $user = $this->createUserWithPermissions(['inventory.create', 'inventory.update']);
        Sanctum::actingAs($user);

        [$inventoryItem] = $this->createInventoryFixture();

        $this->postJson('/api/v1/inventory-items', [
            'product_id' => 999999,
            'storage_location_id' => 999999,
            'quantity' => 0,
            'condition' => str_repeat('x', 41),
        ])->assertUnprocessable()->assertJsonValidationErrors(['product_id', 'storage_location_id', 'quantity', 'condition']);

        $this->patchJson("/api/v1/inventory-items/{$inventoryItem->id}", ['quantity' => 0])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['quantity']);
    }

    public function test_policy_and_role_checks_are_enforced(): void
    {
        [$inventoryItem] = $this->createInventoryFixture();

        Sanctum::actingAs(User::factory()->create());
        $this->getJson('/api/v1/inventory-items')->assertForbidden();

        $viewer = User::factory()->create();
        $viewer->assignRole('viewer');
        Sanctum::actingAs($viewer);

        $this->getJson('/api/v1/inventory-items')->assertOk();
        $this->patchJson("/api/v1/inventory-items/{$inventoryItem->id}", ['quantity' => 8])->assertForbidden();
    }

    public function test_transfer_between_storage_locations_updates_stock_and_logs_movement(): void
    {
        $user = $this->createUserWithPermissions(['inventory.update']);
        Sanctum::actingAs($user);

        [$inventoryItem, $sourceLocation, $targetLocation] = $this->createInventoryFixture(quantity: 5);

        $this->postJson("/api/v1/inventory-items/{$inventoryItem->id}/transfer", [
            'quantity' => 3,
            'target_storage_location_id' => $targetLocation->id,
            'reason' => 'Relocation',
            'request_key' => 'transfer-1',
        ])->assertOk();

        $this->assertDatabaseHas('inventory_items', ['id' => $inventoryItem->id, 'quantity' => 2]);
        $this->assertDatabaseHas('inventory_items', [
            'product_id' => $inventoryItem->product_id,
            'storage_location_id' => $targetLocation->id,
            'quantity' => 3,
        ]);
        $this->assertDatabaseHas('inventory_movements', [
            'inventory_item_id' => $inventoryItem->id,
            'movement_type' => 'transfer',
            'quantity_delta' => -3,
            'from_storage_location_id' => $sourceLocation->id,
            'to_storage_location_id' => $targetLocation->id,
        ]);
    }

    public function test_adjustment_updates_stock_and_creates_single_movement_with_request_key(): void
    {
        $user = $this->createUserWithPermissions(['inventory.update']);
        Sanctum::actingAs($user);

        [$inventoryItem] = $this->createInventoryFixture(quantity: 4);

        $payload = [
            'quantity_delta' => -2,
            'reason' => 'Manual correction',
            'request_key' => 'adjust-1',
        ];

        $this->postJson("/api/v1/inventory-items/{$inventoryItem->id}/adjust-stock", $payload)->assertOk();
        $this->postJson("/api/v1/inventory-items/{$inventoryItem->id}/adjust-stock", $payload)->assertOk();

        $this->assertDatabaseHas('inventory_items', ['id' => $inventoryItem->id, 'quantity' => 2]);
        $this->assertSame(1, InventoryMovement::query()->where('movement_type', 'adjustment')->count());
    }
}
