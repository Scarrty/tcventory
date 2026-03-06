<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreInventoryItemRequest;
use App\Http\Requests\Api\UpdateInventoryItemRequest;
use App\Models\InventoryItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InventoryItemController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(InventoryItem::class, 'inventory_item');
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->integer('per_page', 15);
        $inventoryItems = InventoryItem::query()
            ->with(['product.game', 'product.set', 'storageLocation'])
            ->latest('id')
            ->paginate(max(1, min($perPage, 100)));

        return response()->json([
            'data' => $inventoryItems->items(),
            'meta' => [
                'current_page' => $inventoryItems->currentPage(),
                'per_page' => $inventoryItems->perPage(),
                'total' => $inventoryItems->total(),
            ],
        ]);
    }

    public function store(StoreInventoryItemRequest $request): JsonResponse
    {
        $inventoryItem = InventoryItem::query()
            ->create($request->validated())
            ->load(['product.game', 'product.set', 'storageLocation']);

        return response()->json([
            'data' => $inventoryItem,
        ], 201);
    }

    public function show(InventoryItem $inventoryItem): JsonResponse
    {
        return response()->json([
            'data' => $inventoryItem->load(['product.game', 'product.set', 'storageLocation']),
        ]);
    }

    public function update(UpdateInventoryItemRequest $request, InventoryItem $inventoryItem): JsonResponse
    {
        $inventoryItem->update($request->validated());

        return response()->json([
            'data' => $inventoryItem->fresh()->load(['product.game', 'product.set', 'storageLocation']),
        ]);
    }
}
