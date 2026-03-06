<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Game;
use App\Models\InventoryItem;
use App\Models\Product;
use App\Models\Set;
use App\Models\StorageLocation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class CatalogInventoryApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach ([
            'catalog.view',
            'catalog.create',
            'catalog.update',
            'inventory.view',
            'inventory.create',
            'inventory.update',
        ] as $permissionName) {
            Permission::findOrCreate($permissionName, 'web');
        }
    }

    public function test_index_returns_paginated_games_in_data_meta_structure(): void
    {
        $user = $this->authenticateWithPermissions(['catalog.view']);
        Game::query()->create(['name' => 'Pokemon TCG', 'slug' => 'pokemon-tcg']);

        Sanctum::actingAs($user);

        $this->getJson('/api/v1/games')
            ->assertOk()
            ->assertJsonStructure([
                'data',
                'meta' => ['current_page', 'per_page', 'total'],
            ])
            ->assertJsonPath('data.0.slug', 'pokemon-tcg');
    }

    public function test_authorized_user_can_store_and_update_catalog_resources(): void
    {
        $user = $this->authenticateWithPermissions(['catalog.create', 'catalog.update']);
        Sanctum::actingAs($user);

        $gameResponse = $this->postJson('/api/v1/games', [
            'name' => 'Magic The Gathering',
            'slug' => 'mtg',
        ])->assertCreated();

        $gameId = $gameResponse->json('data.id');

        $setResponse = $this->postJson('/api/v1/sets', [
            'game_id' => $gameId,
            'name' => 'Alpha',
            'code' => 'ALP',
        ])->assertCreated();

        $setId = $setResponse->json('data.id');

        $productResponse = $this->postJson('/api/v1/products', [
            'game_id' => $gameId,
            'set_id' => $setId,
            'name' => 'Black Lotus',
            'product_type' => 'single',
        ])->assertCreated();

        $productId = $productResponse->json('data.id');

        $this->patchJson("/api/v1/products/{$productId}", [
            'rarity' => 'rare',
        ])->assertOk()->assertJsonPath('data.rarity', 'rare');
    }

    public function test_inventory_item_write_operations_validate_relational_and_required_fields(): void
    {
        $user = $this->authenticateWithPermissions(['inventory.create', 'inventory.update']);
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/inventory-items', [
            'product_id' => 999,
            'storage_location_id' => 999,
            'quantity' => 0,
            'condition' => str_repeat('n', 41),
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['product_id', 'storage_location_id', 'quantity', 'condition']);

        $game = Game::query()->create(['name' => 'Pokemon', 'slug' => 'pokemon']);
        $product = Product::query()->create([
            'game_id' => $game->id,
            'name' => 'Booster Pack',
            'product_type' => 'sealed',
            'is_sealed' => true,
        ]);
        $location = StorageLocation::query()->create(['name' => 'Shelf A', 'type' => 'shelf']);

        $response = $this->postJson('/api/v1/inventory-items', [
            'product_id' => $product->id,
            'storage_location_id' => $location->id,
            'quantity' => 2,
            'condition' => 'near_mint',
        ])->assertCreated()->assertJsonPath('data.quantity', 2);

        $inventoryId = $response->json('data.id');

        $this->patchJson("/api/v1/inventory-items/{$inventoryId}", [
            'quantity' => 5,
            'condition' => 'mint',
        ])->assertOk()->assertJsonPath('data.quantity', 5);
    }

    public function test_endpoints_require_authentication(): void
    {
        $this->getJson('/api/v1/games')->assertUnauthorized();
        $this->postJson('/api/v1/games', [
            'name' => 'No Auth',
            'slug' => 'no-auth',
        ])->assertUnauthorized();
    }

    public function test_forbidden_when_user_lacks_required_permissions(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $game = Game::query()->create(['name' => 'Pokemon TCG', 'slug' => 'pokemon-tcg']);

        $this->getJson('/api/v1/games')->assertForbidden();

        $this->patchJson("/api/v1/games/{$game->id}", [
            'name' => 'Pokemon Updated',
        ])->assertForbidden();
    }

    public function test_show_endpoints_return_data_wrapper(): void
    {
        $user = $this->authenticateWithPermissions(['catalog.view', 'inventory.view']);
        Sanctum::actingAs($user);

        $game = Game::query()->create(['name' => 'Lorcana', 'slug' => 'lorcana']);
        $set = Set::query()->create(['game_id' => $game->id, 'name' => 'First Chapter', 'code' => 'FC']);
        $product = Product::query()->create([
            'game_id' => $game->id,
            'set_id' => $set->id,
            'name' => 'Starter Deck',
            'product_type' => 'deck',
        ]);
        $inventoryItem = InventoryItem::query()->create([
            'product_id' => $product->id,
            'quantity' => 1,
            'condition' => 'sealed',
        ]);

        $this->getJson("/api/v1/sets/{$set->id}")->assertOk()->assertJsonPath('data.id', $set->id);
        $this->getJson("/api/v1/products/{$product->id}")->assertOk()->assertJsonPath('data.id', $product->id);
        $this->getJson("/api/v1/inventory-items/{$inventoryItem->id}")->assertOk()->assertJsonPath('data.id', $inventoryItem->id);
    }

    /**
     * @param  list<string>  $permissions
     */
    private function authenticateWithPermissions(array $permissions): User
    {
        $user = User::factory()->create();

        foreach ($permissions as $permissionName) {
            Permission::findOrCreate($permissionName, 'web');
        }

        $user->givePermissionTo($permissions);

        return $user;
    }
}
