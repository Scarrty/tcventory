<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\AdjustInventoryStockRequest;
use App\Http\Requests\Api\StoreInventoryItemRequest;
use App\Http\Requests\Api\TransferInventoryItemRequest;
use App\Http\Requests\Api\UpdateInventoryItemRequest;
use App\Models\InventoryItem;
use App\Services\Audit\HashChainAuditLogger;
use App\Services\Inventory\AdjustInventoryStockService;
use App\Services\Inventory\DeleteInventoryItemService;
use App\Services\Inventory\TransferInventoryItemService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryItemController extends Controller
{
    public function __construct(private readonly HashChainAuditLogger $auditLogger)
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
        $actor = $request->user();

        DB::afterCommit(function () use ($inventoryItem, $actor): void {
            $this->auditLogger->log(
                eventType: 'inventory.item.created',
                auditable: $inventoryItem,
                changes: [
                    'inventory_item_id' => $inventoryItem->id,
                    'after' => [
                        'product_id' => $inventoryItem->product_id,
                        'storage_location_id' => $inventoryItem->storage_location_id,
                        'quantity' => $inventoryItem->quantity,
                        'condition' => $inventoryItem->condition,
                    ],
                ],
                context: ['source' => 'api.v1.inventory-items.store'],
                actor: $actor,
            );
        });

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
        $before = [
            'product_id' => $inventoryItem->product_id,
            'storage_location_id' => $inventoryItem->storage_location_id,
            'quantity' => $inventoryItem->quantity,
            'condition' => $inventoryItem->condition,
        ];

        $inventoryItem->update($request->validated());
        $updatedInventoryItem = $inventoryItem->fresh()->load(['product.game', 'product.set', 'storageLocation']);
        $actor = $request->user();

        DB::afterCommit(function () use ($inventoryItem, $before, $updatedInventoryItem, $actor): void {
            $this->auditLogger->log(
                eventType: 'inventory.item.updated',
                auditable: $inventoryItem,
                changes: [
                    'inventory_item_id' => $inventoryItem->id,
                    'before' => $before,
                    'after' => [
                        'product_id' => $updatedInventoryItem->product_id,
                        'storage_location_id' => $updatedInventoryItem->storage_location_id,
                        'quantity' => $updatedInventoryItem->quantity,
                        'condition' => $updatedInventoryItem->condition,
                    ],
                ],
                context: ['source' => 'api.v1.inventory-items.update'],
                actor: $actor,
            );
        });

        return response()->json([
            'data' => $updatedInventoryItem,
        ]);
    }

    public function destroy(Request $request, InventoryItem $inventoryItem, DeleteInventoryItemService $service): JsonResponse
    {
        $this->authorize('delete', $inventoryItem);

        $changes = [
            'inventory_item_id' => $inventoryItem->id,
            'before' => [
                'product_id' => $inventoryItem->product_id,
                'storage_location_id' => $inventoryItem->storage_location_id,
                'quantity' => $inventoryItem->quantity,
                'condition' => $inventoryItem->condition,
            ],
        ];

        $service->execute($inventoryItem);
        $actor = $request->user();

        DB::afterCommit(function () use ($inventoryItem, $changes, $actor): void {
            $this->auditLogger->log(
                eventType: 'inventory.item.deleted',
                auditable: $inventoryItem,
                changes: $changes,
                context: ['source' => 'api.v1.inventory-items.destroy'],
                actor: $actor,
            );
        });

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
