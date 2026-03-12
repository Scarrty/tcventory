<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\AdjustInventoryStockRequest;
use App\Http\Requests\Api\StoreInventoryItemRequest;
use App\Http\Requests\Api\TransferInventoryItemRequest;
use App\Http\Requests\Api\UpdateInventoryItemRequest;
use App\Models\InventoryItem;
use App\Services\Inventory\AdjustInventoryStockService;
use App\Services\Inventory\DeleteInventoryItemService;
use App\Services\Inventory\TransferInventoryItemService;
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

    public function destroy(InventoryItem $inventoryItem, DeleteInventoryItemService $service): JsonResponse
    {
        $this->authorize('delete', $inventoryItem);

        $service->execute($inventoryItem);

        return response()->json([], 204);
    }

    public function transfer(TransferInventoryItemRequest $request, InventoryItem $inventoryItem, TransferInventoryItemService $service): JsonResponse
    {
        $updatedInventoryItem = $service->execute(
            $inventoryItem,
            $request->integer('quantity'),
            $request->integer('target_storage_location_id'),
            $request->string('reason')->toString() !== '' ? $request->string('reason')->toString() : null,
            $request->string('request_key')->toString() !== '' ? $request->string('request_key')->toString() : null,
            $request->user(),
        );

        return response()->json([
            'data' => $updatedInventoryItem->load(['product.game', 'product.set', 'storageLocation']),
        ]);
    }

    public function adjustStock(AdjustInventoryStockRequest $request, InventoryItem $inventoryItem, AdjustInventoryStockService $service): JsonResponse
    {
        $updatedInventoryItem = $service->execute(
            $inventoryItem,
            $request->integer('quantity_delta'),
            $request->string('reason')->toString() !== '' ? $request->string('reason')->toString() : null,
            $request->string('request_key')->toString() !== '' ? $request->string('request_key')->toString() : null,
            $request->user(),
        );

        return response()->json([
            'data' => $updatedInventoryItem->load(['product.game', 'product.set', 'storageLocation']),
        ]);
    }
}
