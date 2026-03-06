<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StorePurchaseRequest;
use App\Models\Purchase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Purchase::class, 'purchase');
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->integer('per_page', 15);
        $purchases = Purchase::query()->with('items')->latest('id')->paginate(max(1, min($perPage, 100)));

        return response()->json(['data' => $purchases->items(), 'meta' => [
            'current_page' => $purchases->currentPage(),
            'per_page' => $purchases->perPage(),
            'total' => $purchases->total(),
        ]]);
    }

    public function store(StorePurchaseRequest $request): JsonResponse
    {
        $payload = $request->validated();

        if (! empty($payload['request_key'])) {
            $existing = Purchase::query()->where('request_key', $payload['request_key'])->with('items')->first();
            if ($existing instanceof Purchase) {
                return response()->json(['data' => $existing]);
            }
        }

        $purchase = DB::transaction(function () use ($payload): Purchase {
            $subtotal = collect($payload['items'])->sum(fn (array $item): float => ((float) $item['unit_cost_amount']) * ((int) $item['quantity']));

            $purchase = Purchase::query()->create([
                'vendor_name' => $payload['vendor_name'] ?? null,
                'purchased_at' => $payload['purchased_at'],
                'subtotal_amount' => $subtotal,
                'shipping_amount' => $payload['shipping_amount'] ?? 0,
                'fee_amount' => $payload['fee_amount'] ?? 0,
                'tax_amount' => $payload['tax_amount'] ?? 0,
                'total_amount' => $subtotal + (float) ($payload['shipping_amount'] ?? 0) + (float) ($payload['fee_amount'] ?? 0) + (float) ($payload['tax_amount'] ?? 0),
                'currency' => strtoupper((string) ($payload['currency'] ?? 'EUR')),
                'notes' => $payload['notes'] ?? null,
                'request_key' => $payload['request_key'] ?? null,
            ]);

            foreach ($payload['items'] as $item) {
                $purchase->items()->create([
                    'product_id' => $item['product_id'],
                    'inventory_item_id' => $item['inventory_item_id'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_cost_amount' => $item['unit_cost_amount'],
                    'line_total_amount' => ((float) $item['unit_cost_amount']) * ((int) $item['quantity']),
                ]);
            }

            return $purchase->load('items');
        });

        return response()->json(['data' => $purchase], 201);
    }
}
