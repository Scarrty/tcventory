<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\AuditEvent;
use App\Models\InventoryItem;
use App\Models\StorageLocation;
use App\Models\User;
use Carbon\CarbonImmutable;
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
            ->assertJsonPath('data.latest_inventory_valuation', 28)
            ->assertJsonPath('data.kpis.sale_gross_total', 30)
            ->assertJsonPath('data.kpis.fee_burden_total', 3)
            ->assertJsonPath('data.kpis.unrealized_profit_loss', 5);

        $this->assertSame(3, AuditEvent::query()->count());
        $this->assertSame(
            ['finance.purchase.created', 'finance.sale.created', 'finance.valuation.created'],
            AuditEvent::query()->orderBy('id')->pluck('event_type')->all(),
        );
    }

    public function test_finance_summary_supports_custom_period_and_channel_breakdown(): void
    {
        $user = $this->createUserWithPermissions(['finance.view', 'finance.create']);
        Sanctum::actingAs($user);

        [, , $product] = $this->createCatalogFixture();
        $location = StorageLocation::factory()->create();
        $inventoryItem = InventoryItem::factory()->for($product)->for($location)->create();

        $inside = CarbonImmutable::parse('2026-03-10T12:00:00Z');
        $outside = CarbonImmutable::parse('2026-02-10T12:00:00Z');

        $this->postJson('/api/v1/purchases', [
            'purchased_at' => $inside->toISOString(),
            'items' => [[
                'product_id' => $product->id,
                'inventory_item_id' => $inventoryItem->id,
                'quantity' => 1,
                'unit_cost_amount' => 10,
            ]],
        ])->assertCreated();

        $this->postJson('/api/v1/purchases', [
            'purchased_at' => $outside->toISOString(),
            'items' => [[
                'product_id' => $product->id,
                'inventory_item_id' => $inventoryItem->id,
                'quantity' => 1,
                'unit_cost_amount' => 99,
            ]],
        ])->assertCreated();

        $this->postJson('/api/v1/sales', [
            'channel' => 'ebay',
            'sold_at' => $inside->toISOString(),
            'fee_amount' => 2,
            'tax_amount' => 1,
            'items' => [[
                'product_id' => $product->id,
                'inventory_item_id' => $inventoryItem->id,
                'quantity' => 1,
                'unit_price_amount' => 30,
            ]],
        ])->assertCreated();

        $this->postJson('/api/v1/sales', [
            'channel' => 'cardmarket',
            'sold_at' => $inside->toISOString(),
            'items' => [[
                'product_id' => $product->id,
                'inventory_item_id' => $inventoryItem->id,
                'quantity' => 1,
                'unit_price_amount' => 5,
            ]],
        ])->assertCreated();

        $this->postJson('/api/v1/sales', [
            'channel' => 'ebay',
            'sold_at' => $outside->toISOString(),
            'items' => [[
                'product_id' => $product->id,
                'inventory_item_id' => $inventoryItem->id,
                'quantity' => 1,
                'unit_price_amount' => 777,
            ]],
        ])->assertCreated();

        $response = $this->getJson('/api/v1/reports/finance-summary?period=custom&from_date=2026-03-01&to_date=2026-03-31&group_by=channel&channel=ebay');

        $response->assertOk()
            ->assertJsonPath('data.kpis.purchase_total', 10)
            ->assertJsonPath('data.kpis.sale_gross_total', 30)
            ->assertJsonPath('data.kpis.sale_net_total', 27)
            ->assertJsonPath('data.kpis.fee_burden_total', 2)
            ->assertJsonPath('data.kpis.tax_burden_total', 1)
            ->assertJsonCount(2, 'data.breakdown.by_channel');
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

    public function test_finance_summary_validation_for_period_filter_combinations(): void
    {
        $user = $this->createUserWithPermissions(['finance.view']);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/reports/finance-summary?period=custom')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['period']);

        $this->getJson('/api/v1/reports/finance-summary?period=month&from_date=2026-03-01&to_date=2026-03-31')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['period']);
    }

    public function test_finance_audit_chain_links_events_and_verify_command_passes(): void
    {
        $user = $this->createUserWithPermissions(['finance.view', 'finance.create']);
        Sanctum::actingAs($user);

        [, , $product] = $this->createCatalogFixture();
        $location = StorageLocation::factory()->create();
        $inventoryItem = InventoryItem::factory()->for($product)->for($location)->create();

        $this->postJson('/api/v1/purchases', [
            'purchased_at' => now()->toISOString(),
            'request_key' => 'chain-purchase',
            'items' => [[
                'product_id' => $product->id,
                'inventory_item_id' => $inventoryItem->id,
                'quantity' => 1,
                'unit_cost_amount' => 11,
            ]],
        ])->assertCreated();

        $this->postJson('/api/v1/sales', [
            'sold_at' => now()->toISOString(),
            'request_key' => 'chain-sale',
            'items' => [[
                'product_id' => $product->id,
                'inventory_item_id' => $inventoryItem->id,
                'quantity' => 1,
                'unit_price_amount' => 22,
            ]],
        ])->assertCreated();

        $this->postJson('/api/v1/valuations', [
            'inventory_item_id' => $inventoryItem->id,
            'value_amount' => 21,
            'valued_at' => now()->toISOString(),
        ])->assertCreated();

        $events = AuditEvent::query()->orderBy('id')->get();

        $this->assertCount(3, $events);
        $this->assertNull($events[0]->previous_hash);
        $this->assertSame($events[0]->event_hash, $events[1]->previous_hash);
        $this->assertSame($events[1]->event_hash, $events[2]->previous_hash);

        $this->artisan('audit:verify-chain')->assertSuccessful();
    }
}
