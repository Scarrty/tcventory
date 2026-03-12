<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\InteractsWithApiPagination;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreSaleRequest;
use App\Models\Sale;
use App\Services\Audit\HashChainAuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    use InteractsWithApiPagination;

    public function __construct(private readonly HashChainAuditLogger $auditLogger)
    {
        $this->authorizeResource(Sale::class, 'sale');
    }

    public function index(Request $request): JsonResponse
    {
        $sales = Sale::query()->with('items')->latest('id')->paginate($this->resolvePerPage($request));

        return $this->paginatedResponse($sales);
    }

    public function store(StoreSaleRequest $request): JsonResponse
    {
        $payload = $request->validated();

        if (! empty($payload['request_key'])) {
            $existing = Sale::query()->where('request_key', $payload['request_key'])->with('items')->first();
            if ($existing instanceof Sale) {
                return response()->json(['data' => $existing]);
            }
        }

        $sale = DB::transaction(function () use ($payload): Sale {
            $gross = collect($payload['items'])->sum(fn (array $item): float => ((float) $item['unit_price_amount']) * ((int) $item['quantity']));

            $sale = Sale::query()->create([
                'channel' => $payload['channel'] ?? null,
                'sold_at' => $payload['sold_at'],
                'gross_amount' => $gross,
                'shipping_amount' => $payload['shipping_amount'] ?? 0,
                'fee_amount' => $payload['fee_amount'] ?? 0,
                'tax_amount' => $payload['tax_amount'] ?? 0,
                'net_amount' => $gross - (float) ($payload['fee_amount'] ?? 0) - (float) ($payload['tax_amount'] ?? 0) - (float) ($payload['shipping_amount'] ?? 0),
                'currency' => strtoupper((string) ($payload['currency'] ?? 'EUR')),
                'notes' => $payload['notes'] ?? null,
                'request_key' => $payload['request_key'] ?? null,
            ]);

            foreach ($payload['items'] as $item) {
                $sale->items()->create([
                    'product_id' => $item['product_id'],
                    'inventory_item_id' => $item['inventory_item_id'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_price_amount' => $item['unit_price_amount'],
                    'line_total_amount' => ((float) $item['unit_price_amount']) * ((int) $item['quantity']),
                ]);
            }

            return $sale->load('items');
        });

        $actor = $request->user();
        DB::afterCommit(function () use ($sale, $actor): void {
            $this->auditLogger->log(
                eventType: 'finance.sale.created',
                auditable: $sale,
                changes: [
                    'sale_id' => $sale->id,
                    'request_key' => $sale->request_key,
                    'totals' => [
                        'gross_amount' => $sale->gross_amount,
                        'shipping_amount' => $sale->shipping_amount,
                        'fee_amount' => $sale->fee_amount,
                        'tax_amount' => $sale->tax_amount,
                        'net_amount' => $sale->net_amount,
                        'currency' => $sale->currency,
                    ],
                    'items' => $sale->items->map(fn ($item): array => [
                        'product_id' => $item->product_id,
                        'inventory_item_id' => $item->inventory_item_id,
                        'quantity' => $item->quantity,
                        'unit_price_amount' => $item->unit_price_amount,
                        'line_total_amount' => $item->line_total_amount,
                    ])->all(),
                ],
                context: [
                    'source' => 'api.v1.sales.store',
                    'channel' => $sale->channel,
                ],
                actor: $actor,
            );
        });

        return response()->json(['data' => $sale], 201);
    }
}
