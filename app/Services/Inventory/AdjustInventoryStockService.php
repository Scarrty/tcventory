<?php

declare(strict_types=1);

namespace App\Services\Inventory;

use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use App\Services\Audit\HashChainAuditLogger;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AdjustInventoryStockService
{
    public function __construct(private readonly HashChainAuditLogger $auditLogger) {}

    public function execute(
        InventoryItem $inventoryItem,
        int $quantityDelta,
        ?string $reason = null,
        ?string $requestKey = null,
        ?Authenticatable $actor = null,
    ): InventoryItem {
        return DB::transaction(function () use ($inventoryItem, $quantityDelta, $reason, $requestKey, $actor): InventoryItem {
            /** @var InventoryItem $lockedItem */
            $lockedItem = InventoryItem::query()->lockForUpdate()->findOrFail($inventoryItem->id);
            $quantityBefore = $lockedItem->quantity;

            if ($quantityDelta === 0) {
                throw ValidationException::withMessages([
                    'quantity_delta' => 'The quantity delta must not be zero.',
                ]);
            }

            if ($requestKey !== null) {
                $existingMovement = $this->findIdempotentMovement($lockedItem->id, 'adjustment', $requestKey);
                if ($existingMovement instanceof InventoryMovement) {
                    return InventoryItem::query()->findOrFail($lockedItem->id);
                }
            }

            $newQuantity = $lockedItem->quantity + $quantityDelta;
            if ($newQuantity < 0) {
                throw ValidationException::withMessages([
                    'quantity_delta' => 'The adjustment would result in negative stock.',
                ]);
            }

            $lockedItem->forceFill(['quantity' => $newQuantity])->save();

            InventoryMovement::query()->create([
                'inventory_item_id' => $lockedItem->id,
                'movement_type' => 'adjustment',
                'quantity_delta' => $quantityDelta,
                'from_storage_location_id' => $lockedItem->storage_location_id,
                'to_storage_location_id' => $lockedItem->storage_location_id,
                'reason' => $reason,
                'metadata' => [
                    'request_key' => $requestKey,
                ],
                'occurred_at' => now(),
            ]);

            DB::afterCommit(function () use ($lockedItem, $quantityDelta, $quantityBefore, $newQuantity, $reason, $requestKey, $actor): void {
                $this->auditLogger->log(
                    eventType: 'inventory.stock.adjusted',
                    auditable: $lockedItem,
                    changes: [
                        'inventory_item_id' => $lockedItem->id,
                        'request_key' => $requestKey,
                        'quantity_delta' => $quantityDelta,
                        'reason' => $reason,
                        'before' => [
                            'quantity' => $quantityBefore,
                        ],
                        'after' => [
                            'quantity' => $newQuantity,
                        ],
                    ],
                    context: ['source' => 'api.v1.inventory.adjust-stock'],
                    actor: $actor,
                );
            });

            return InventoryItem::query()->findOrFail($lockedItem->id)->fresh();
        });
    }

    private function findIdempotentMovement(int $inventoryItemId, string $movementType, string $requestKey): ?InventoryMovement
    {
        return InventoryMovement::query()
            ->where('inventory_item_id', $inventoryItemId)
            ->where('movement_type', $movementType)
            ->where('metadata->request_key', $requestKey)
            ->latest('id')
            ->first();
    }
}
