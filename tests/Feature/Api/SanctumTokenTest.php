<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SanctumTokenTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_a_personal_access_token(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/v1/tokens', [
            'token_name' => 'feature-test-token',
        ]);

        $response
            ->assertOk()
            ->assertJsonStructure([
                'token',
                'token_type',
            ]);

        $this->assertDatabaseCount('personal_access_tokens', 1);
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
