<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SanctumTokenTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_a_personal_access_token(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/tokens', [
            'token_name' => 'feature-test-token',
            'abilities' => ['inventory:read', 'inventory:write'],
            'expires_in_minutes' => 120,
        ]);

        $response
            ->assertOk()
            ->assertJsonStructure([
                'token',
                'token_type',
                'abilities',
                'expires_at',
            ])
            ->assertJsonPath('abilities.0', 'inventory:read')
            ->assertJsonPath('abilities.1', 'inventory:write');

        $this->assertDatabaseCount('personal_access_tokens', 1);
    }

    public function test_token_endpoint_rejects_too_many_abilities(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $abilities = array_map(static fn (int $i): string => "ability:{$i}", range(1, 21));

        $this->postJson('/api/v1/tokens', [
            'token_name' => 'feature-test-token',
            'abilities' => $abilities,
        ])->assertUnprocessable();
    }

    public function test_user_can_access_protected_me_endpoint_with_valid_bearer_token_and_role(): void
    {
        $user = User::factory()->create();
        Role::create(['name' => 'user']);
        $user->assignRole('user');

        $token = $user->createToken('feature-test')->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/v1/me')
            ->assertOk()
            ->assertJsonPath('email', $user->email)
            ->assertJsonPath('roles.0', 'user');
    }

    public function test_me_endpoint_is_unauthorized_without_authentication(): void
    {
        $this->getJson('/api/v1/me')->assertUnauthorized();
    }

    public function test_me_endpoint_is_forbidden_for_authenticated_user_without_required_role(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('feature-test')->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/v1/me')
            ->assertForbidden();
    }
}
