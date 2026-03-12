<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\AuditEvent;
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

    public function test_inventory_transfer_and_adjustment_emit_audit_chain_events(): void
    {
        $user = $this->createUserWithPermissions(['inventory.update']);
        Sanctum::actingAs($user);

        [$inventoryItem, $sourceLocation, $targetLocation] = $this->createInventoryFixture(quantity: 6);

        $this->postJson("/api/v1/inventory-items/{$inventoryItem->id}/transfer", [
            'quantity' => 2,
            'target_storage_location_id' => $targetLocation->id,
            'reason' => 'Audit transfer',
            'request_key' => 'audit-transfer-1',
        ])->assertOk();

        $this->postJson("/api/v1/inventory-items/{$inventoryItem->id}/transfer", [
            'quantity' => 2,
            'target_storage_location_id' => $targetLocation->id,
            'reason' => 'Audit transfer',
            'request_key' => 'audit-transfer-1',
        ])->assertOk();

        $this->postJson("/api/v1/inventory-items/{$inventoryItem->id}/adjust-stock", [
            'quantity_delta' => -1,
            'reason' => 'Audit adjust',
            'request_key' => 'audit-adjust-1',
        ])->assertOk();

        $this->postJson("/api/v1/inventory-items/{$inventoryItem->id}/adjust-stock", [
            'quantity_delta' => -1,
            'reason' => 'Audit adjust',
            'request_key' => 'audit-adjust-1',
        ])->assertOk();

        $events = AuditEvent::query()->orderBy('id')->get();

        $this->assertCount(2, $events);
        $this->assertSame(
            ['inventory.transfer.executed', 'inventory.stock.adjusted'],
            $events->pluck('event_type')->all(),
        );
        $this->assertNull($events[0]->previous_hash);
        $this->assertSame($events[0]->event_hash, $events[1]->previous_hash);

        $this->assertSame($sourceLocation->id, $events[0]->changes['from_storage_location_id']);
        $this->assertSame($targetLocation->id, $events[0]->changes['to_storage_location_id']);
        $this->assertSame(-1, $events[1]->changes['quantity_delta']);

        $this->artisan('audit:verify-chain')->assertSuccessful();
    }

    public function test_destroy_soft_deletes_inventory_item_and_archives_movement(): void
    {
        $user = $this->createUserWithPermissions(['inventory.delete']);
        Sanctum::actingAs($user);

        [$inventoryItem] = $this->createInventoryFixture(quantity: 4);

        $this->deleteJson("/api/v1/inventory-items/{$inventoryItem->id}")->assertNoContent();

        $this->assertSoftDeleted('inventory_items', ['id' => $inventoryItem->id]);
        $this->assertDatabaseHas('inventory_movements', [
            'inventory_item_id' => $inventoryItem->id,
            'movement_type' => 'deletion',
            'quantity_delta' => -4,
        ]);
    }

    public function test_destroy_returns_forbidden_for_unauthorized_role(): void
    {
        [$inventoryItem] = $this->createInventoryFixture(quantity: 4);

        $viewer = User::factory()->create();
        $viewer->assignRole('viewer');
        Sanctum::actingAs($viewer);

        $this->deleteJson("/api/v1/inventory-items/{$inventoryItem->id}")->assertForbidden();

        $this->assertDatabaseHas('inventory_items', ['id' => $inventoryItem->id, 'deleted_at' => null]);
        $this->assertDatabaseMissing('inventory_movements', [
            'inventory_item_id' => $inventoryItem->id,
            'movement_type' => 'deletion',
        ]);
    }

    public function test_destroy_returns_not_found_for_unknown_id(): void
    {
        $user = $this->createUserWithPermissions(['inventory.delete']);
        Sanctum::actingAs($user);

        $this->deleteJson('/api/v1/inventory-items/999999')->assertNotFound();
    }

    public function test_destroy_returns_unprocessable_when_inventory_item_has_existing_movements(): void
    {
        $user = $this->createUserWithPermissions(['inventory.delete']);
        Sanctum::actingAs($user);

        [$inventoryItem] = $this->createInventoryFixture(quantity: 4);
        $this->createInventoryMovementForItem($inventoryItem);

        $this->deleteJson("/api/v1/inventory-items/{$inventoryItem->id}")
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['inventory_item']);

        $this->assertDatabaseHas('inventory_items', ['id' => $inventoryItem->id, 'deleted_at' => null]);
        $this->assertDatabaseMissing('inventory_movements', [
            'inventory_item_id' => $inventoryItem->id,
            'movement_type' => 'deletion',
        ]);
    }
}
