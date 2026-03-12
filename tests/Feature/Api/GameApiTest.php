<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\AuditEvent;
use App\Models\Game;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\ApiTestData;
use Tests\TestCase;

class GameApiTest extends TestCase
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

        Game::factory()->create(['name' => 'Pokemon TCG', 'slug' => 'pokemon-tcg']);

        $this->getJson('/api/v1/games')
            ->assertOk()
            ->assertJsonPath('data.0.slug', 'pokemon-tcg')
            ->assertJsonStructure(['data', 'meta' => ['current_page', 'per_page', 'total']]);

        $created = $this->postJson('/api/v1/games', [
            'name' => 'Magic The Gathering',
            'slug' => 'mtg',
        ])->assertCreated();

        $gameId = $created->json('data.id');

        $this->getJson("/api/v1/games/{$gameId}")
            ->assertOk()
            ->assertJsonPath('data.slug', 'mtg');

        $this->patchJson("/api/v1/games/{$gameId}", [
            'name' => 'Magic',
        ])->assertOk()->assertJsonPath('data.name', 'Magic');
    }

    public function test_store_update_and_destroy_emit_audit_chain_events(): void
    {
        $user = $this->createUserWithPermissions(['catalog.create', 'catalog.update', 'catalog.delete']);
        Sanctum::actingAs($user);

        $created = $this->postJson('/api/v1/games', [
            'name' => 'Lorcana',
            'slug' => 'lorcana',
        ])->assertCreated();

        $gameId = $created->json('data.id');

        $this->patchJson("/api/v1/games/{$gameId}", ['name' => 'Disney Lorcana'])->assertOk();
        $this->deleteJson("/api/v1/games/{$gameId}")->assertNoContent();

        $events = AuditEvent::query()->orderBy('id')->get();

        $this->assertSame(
            ['catalog.game.created', 'catalog.game.updated', 'catalog.game.deleted'],
            $events->pluck('event_type')->all(),
        );
        $this->assertNull($events[0]->previous_hash);
        $this->assertSame($events[0]->event_hash, $events[1]->previous_hash);
        $this->assertSame($events[1]->event_hash, $events[2]->previous_hash);

        $this->assertSame('Lorcana', $events[0]->changes['after']['name']);
        $this->assertSame('Disney Lorcana', $events[1]->changes['after']['name']);
        $this->assertSame('Disney Lorcana', $events[2]->changes['before']['name']);
    }

    public function test_store_and_update_validate_payloads(): void
    {
        $user = $this->createUserWithPermissions(['catalog.create', 'catalog.update']);
        Sanctum::actingAs($user);

        $game = Game::factory()->create(['slug' => 'taken-slug']);

        $this->postJson('/api/v1/games', ['name' => '', 'slug' => 'taken-slug'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'slug']);

        $this->patchJson("/api/v1/games/{$game->id}", ['name' => str_repeat('x', 121)])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    public function test_policy_and_role_checks_are_enforced(): void
    {
        $game = Game::factory()->create();

        Sanctum::actingAs(User::factory()->create());
        $this->getJson('/api/v1/games')->assertForbidden();

        $viewer = User::factory()->create();
        $viewer->assignRole('viewer');
        Sanctum::actingAs($viewer);

        $this->getJson('/api/v1/games')->assertOk();
        $this->postJson('/api/v1/games', ['name' => 'Unauthorized', 'slug' => 'unauthorized'])->assertForbidden();
        $this->patchJson("/api/v1/games/{$game->id}", ['name' => 'Nope'])->assertForbidden();
    }

    public function test_destroy_soft_deletes_game_for_authorized_user(): void
    {
        $user = $this->createUserWithPermissions(['catalog.delete']);
        Sanctum::actingAs($user);

        $game = Game::factory()->create();

        $this->deleteJson("/api/v1/games/{$game->id}")->assertNoContent();

        $this->assertSoftDeleted('games', ['id' => $game->id]);
    }

    public function test_destroy_returns_forbidden_for_unauthorized_role(): void
    {
        $viewer = User::factory()->create();
        $viewer->assignRole('viewer');
        Sanctum::actingAs($viewer);

        $game = Game::factory()->create();

        $this->deleteJson("/api/v1/games/{$game->id}")->assertForbidden();
        $this->assertDatabaseHas('games', ['id' => $game->id, 'deleted_at' => null]);
    }

    public function test_destroy_returns_not_found_for_unknown_id(): void
    {
        $user = $this->createUserWithPermissions(['catalog.delete']);
        Sanctum::actingAs($user);

        $this->deleteJson('/api/v1/games/999999')->assertNotFound();
    }
}
