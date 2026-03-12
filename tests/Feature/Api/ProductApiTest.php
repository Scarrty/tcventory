<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\AuditEvent;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\ApiTestData;
use Tests\TestCase;

class ProductApiTest extends TestCase
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
        $user = $this->createUserWithPermissions(['catalog.view', 'catalog.create', 'catalog.update']);
        Sanctum::actingAs($user);

        [$game, $set] = $this->createCatalogFixture();

        Product::factory()->for($game)->for($set)->create(['name' => 'Black Lotus']);

        $this->getJson('/api/v1/products')->assertOk()->assertJsonPath('data.0.name', 'Black Lotus');

        $created = $this->postJson('/api/v1/products', [
            'game_id' => $game->id,
            'set_id' => $set->id,
            'name' => 'Time Walk',
            'product_type' => 'single',
        ])->assertCreated();

        $productId = $created->json('data.id');

        $this->getJson("/api/v1/products/{$productId}")->assertOk()->assertJsonPath('data.name', 'Time Walk');

        $this->patchJson("/api/v1/products/{$productId}", ['rarity' => 'rare'])
            ->assertOk()
            ->assertJsonPath('data.rarity', 'rare');
    }

    public function test_store_update_and_destroy_emit_audit_chain_events(): void
    {
        $user = $this->createUserWithPermissions(['catalog.create', 'catalog.update', 'catalog.delete']);
        Sanctum::actingAs($user);

        [$game, $set] = $this->createCatalogFixture();

        $created = $this->postJson('/api/v1/products', [
            'game_id' => $game->id,
            'set_id' => $set->id,
            'name' => 'Counterspell',
            'product_type' => 'single',
        ])->assertCreated();

        $productId = $created->json('data.id');

        $this->patchJson("/api/v1/products/{$productId}", ['rarity' => 'uncommon'])->assertOk();
        $this->deleteJson("/api/v1/products/{$productId}")->assertNoContent();

        $events = AuditEvent::query()->orderBy('id')->get();

        $this->assertSame(
            ['catalog.product.created', 'catalog.product.updated', 'catalog.product.deleted'],
            $events->pluck('event_type')->all(),
        );
        $this->assertNull($events[0]->previous_hash);
        $this->assertSame($events[0]->event_hash, $events[1]->previous_hash);
        $this->assertSame($events[1]->event_hash, $events[2]->previous_hash);

        $this->assertSame('Counterspell', $events[0]->changes['after']['name']);
        $this->assertSame('uncommon', $events[1]->changes['after']['rarity']);
        $this->assertSame('Counterspell', $events[2]->changes['before']['name']);
    }

    public function test_store_and_update_validate_payloads(): void
    {
        $user = $this->createUserWithPermissions(['catalog.create', 'catalog.update']);
        Sanctum::actingAs($user);

        [$game, $set] = $this->createCatalogFixture();

        $product = Product::factory()->for($game)->for($set)->create();

        $this->postJson('/api/v1/products', [
            'game_id' => $game->id,
            'set_id' => $set->id,
            'name' => '',
            'product_type' => 'single',
            'is_sealed' => 'invalid',
        ])->assertUnprocessable()->assertJsonValidationErrors(['name', 'is_sealed']);

        $this->patchJson("/api/v1/products/{$product->id}", ['set_id' => 999999])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['set_id']);
    }

    public function test_policy_and_role_checks_are_enforced(): void
    {
        $product = Product::factory()->create();

        Sanctum::actingAs(User::factory()->create());
        $this->getJson('/api/v1/products')->assertForbidden();

        $viewer = User::factory()->create();
        $viewer->assignRole('viewer');
        Sanctum::actingAs($viewer);

        $this->getJson('/api/v1/products')->assertOk();
        $this->patchJson("/api/v1/products/{$product->id}", ['rarity' => 'mythic'])->assertForbidden();
    }

    public function test_destroy_soft_deletes_product_for_authorized_user(): void
    {
        $user = $this->createUserWithPermissions(['catalog.delete']);
        Sanctum::actingAs($user);

        $product = Product::factory()->create();

        $this->deleteJson("/api/v1/products/{$product->id}")->assertNoContent();

        $this->assertSoftDeleted('products', ['id' => $product->id]);
    }

    public function test_destroy_returns_forbidden_for_unauthorized_role(): void
    {
        $viewer = User::factory()->create();
        $viewer->assignRole('viewer');
        Sanctum::actingAs($viewer);

        $product = Product::factory()->create();

        $this->deleteJson("/api/v1/products/{$product->id}")->assertForbidden();
        $this->assertDatabaseHas('products', ['id' => $product->id, 'deleted_at' => null]);
    }

    public function test_destroy_returns_not_found_for_unknown_id(): void
    {
        $user = $this->createUserWithPermissions(['catalog.delete']);
        Sanctum::actingAs($user);

        $this->deleteJson('/api/v1/products/999999')->assertNotFound();
    }
}
