<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Set;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\ApiTestData;
use Tests\TestCase;

class SetApiTest extends TestCase
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

        [$game] = $this->createCatalogFixture();
        Set::factory()->for($game)->create(['name' => 'First Chapter', 'code' => 'FC']);

        $this->getJson('/api/v1/sets')->assertOk()->assertJsonPath('data.0.code', 'FC');

        $created = $this->postJson('/api/v1/sets', [
            'game_id' => $game->id,
            'name' => 'Second Chapter',
            'code' => 'SC',
        ])->assertCreated();

        $setId = $created->json('data.id');

        $this->getJson("/api/v1/sets/{$setId}")->assertOk()->assertJsonPath('data.code', 'SC');

        $this->patchJson("/api/v1/sets/{$setId}", ['name' => 'Second Chapter Revised'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Second Chapter Revised');
    }

    public function test_store_and_update_validate_payloads(): void
    {
        $user = $this->createUserWithPermissions(['catalog.create', 'catalog.update']);
        Sanctum::actingAs($user);

        [$game] = $this->createCatalogFixture();
        $set = Set::factory()->for($game)->create(['code' => 'ALP']);

        $this->postJson('/api/v1/sets', ['game_id' => $game->id, 'name' => '', 'code' => 'ALP'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'code']);

        $this->patchJson("/api/v1/sets/{$set->id}", ['release_date' => 'not-a-date'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['release_date']);
    }

    public function test_policy_and_role_checks_are_enforced(): void
    {
        $set = Set::factory()->create();

        Sanctum::actingAs(User::factory()->create());
        $this->getJson('/api/v1/sets')->assertForbidden();

        $viewer = User::factory()->create();
        $viewer->assignRole('viewer');
        Sanctum::actingAs($viewer);

        $this->getJson('/api/v1/sets')->assertOk();
        $this->patchJson("/api/v1/sets/{$set->id}", ['name' => 'Nope'])->assertForbidden();
    }

    public function test_destroy_soft_deletes_set_for_authorized_user(): void
    {
        $user = $this->createUserWithPermissions(['catalog.delete']);
        Sanctum::actingAs($user);

        $set = Set::factory()->create();

        $this->deleteJson("/api/v1/sets/{$set->id}")->assertNoContent();

        $this->assertSoftDeleted('sets', ['id' => $set->id]);
    }

    public function test_destroy_returns_forbidden_for_unauthorized_role(): void
    {
        $viewer = User::factory()->create();
        $viewer->assignRole('viewer');
        Sanctum::actingAs($viewer);

        $set = Set::factory()->create();

        $this->deleteJson("/api/v1/sets/{$set->id}")->assertForbidden();
        $this->assertDatabaseHas('sets', ['id' => $set->id, 'deleted_at' => null]);
    }

    public function test_destroy_returns_not_found_for_unknown_id(): void
    {
        $user = $this->createUserWithPermissions(['catalog.delete']);
        Sanctum::actingAs($user);

        $this->deleteJson('/api/v1/sets/999999')->assertNotFound();
    }
}
