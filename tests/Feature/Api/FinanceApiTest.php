<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\InventoryItem;
use App\Models\StorageLocation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\ApiTestData;
use Tests\TestCase;

class FinanceApiTest extends TestCase
{
    use ApiTestData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRolesAndPermissions();
    }

    public function test_purchase_sale_and_valuation_flows_with_finance_summary(): void
    {
        $user = $this->createUserWithPermissions(['finance.view', 'finance.create']);
        Sanctum::actingAs($user);

        [, , $product] = $this->createCatalogFixture();
        $location = StorageLocation::factory()->create();
        $inventoryItem = InventoryItem::factory()->for($product)->for($location)->create();

        $purchasePayload = [
            'vendor_name' => 'Cardmarket',
            'purchased_at' => now()->toISOString(),
            'shipping_amount' => 2,
            'fee_amount' => 1,
            'tax_amount' => 0,
            'request_key' => 'purchase-1',
            'items' => [[
                'product_id' => $product->id,
                'inventory_item_id' => $inventoryItem->id,
                'quantity' => 2,
                'unit_cost_amount' => 10,
            ]],
        ];

        $this->postJson('/api/v1/purchases', $purchasePayload)->assertCreated()->assertJsonPath('data.total_amount', '23.00');
        $this->postJson('/api/v1/purchases', $purchasePayload)->assertOk();
        $this->assertSame(1, \App\Models\Purchase::query()->count());

        $this->postJson('/api/v1/sales', [
            'channel' => 'ebay',
            'sold_at' => now()->toISOString(),
            'shipping_amount' => 1,
            'fee_amount' => 3,
            'tax_amount' => 0,
            'items' => [[
                'product_id' => $product->id,
                'inventory_item_id' => $inventoryItem->id,
                'quantity' => 1,
                'unit_price_amount' => 30,
            ]],
        ])->assertCreated()->assertJsonPath('data.net_amount', '26.00');

        $this->postJson('/api/v1/valuations', [
            'inventory_item_id' => $inventoryItem->id,
            'value_amount' => 28,
            'source' => 'market',
            'valued_at' => now()->toISOString(),
        ])->assertCreated();

        $this->getJson('/api/v1/reports/finance-summary')
            ->assertOk()
            ->assertJsonPath('data.purchase_total', 23)
            ->assertJsonPath('data.sale_net_total', 26)
            ->assertJsonPath('data.realized_profit_loss', 3)
            ->assertJsonPath('data.latest_inventory_valuation', 28);
    }

    public function test_finance_permissions_are_enforced(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/v1/purchases')->assertForbidden();
        $this->postJson('/api/v1/sales', [])->assertForbidden();
        $this->getJson('/api/v1/reports/finance-summary')->assertForbidden();
    }

    public function test_finance_payload_validation(): void
    {
        $user = $this->createUserWithPermissions(['finance.create']);
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/purchases', [
            'purchased_at' => 'not-a-date',
            'items' => [],
        ])->assertUnprocessable()->assertJsonValidationErrors(['purchased_at', 'items']);
    }
}
