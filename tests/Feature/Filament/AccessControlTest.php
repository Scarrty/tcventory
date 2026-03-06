<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccessControlTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
        $this->withoutVite();
    }

    public function test_user_without_admin_access_cannot_open_admin_panel(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('catalog.view');

        $response = $this->actingAs($user)->get('/admin');

        $response->assertForbidden();
    }

    public function test_admin_can_access_catalog_and_inventory_resources(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $this->actingAs($user)
            ->get(route('filament.admin.resources.games.index'))
            ->assertOk();

        $this->actingAs($user)
            ->get(route('filament.admin.resources.storage-locations.index'))
            ->assertOk();
    }

    public function test_catalog_only_user_cannot_access_inventory_resource(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo(['admin.access', 'catalog.view']);

        $this->actingAs($user)
            ->get(route('filament.admin.resources.games.index'))
            ->assertOk();

        $this->actingAs($user)
            ->get(route('filament.admin.resources.storage-locations.index'))
            ->assertForbidden();
    }
}
