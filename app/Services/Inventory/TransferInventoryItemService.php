<?php

declare(strict_types=1);

namespace App\Services\Inventory;

use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use App\Models\StorageLocation;
use App\Services\Audit\HashChainAuditLogger;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TransferInventoryItemService
{
    public function __construct(private readonly HashChainAuditLogger $auditLogger) {}

    public function execute(
        InventoryItem $inventoryItem,
        int $quantity,
        int $targetStorageLocationId,
        ?string $reason = null,
        ?string $requestKey = null,
        ?Authenticatable $actor = null,
    ): InventoryItem {
        return DB::transaction(function () use ($inventoryItem, $quantity, $targetStorageLocationId, $reason, $requestKey, $actor): InventoryItem {
            /** @var InventoryItem $lockedItem */
            $lockedItem = InventoryItem::query()->lockForUpdate()->findOrFail($inventoryItem->id);
            $sourceLocationId = $lockedItem->storage_location_id;
            $sourceQuantityBefore = $lockedItem->quantity;

            if ($quantity < 1) {
                throw ValidationException::withMessages([
                    'quantity' => 'The quantity must be at least 1.',
                ]);
            }

            if ($lockedItem->quantity < $quantity) {
                throw ValidationException::withMessages([
                    'quantity' => 'The requested transfer quantity exceeds available stock.',
                ]);
            }

            $targetLocationExists = StorageLocation::query()->whereKey($targetStorageLocationId)->exists();
            if (! $targetLocationExists) {
                throw ValidationException::withMessages([
                    'target_storage_location_id' => 'The target storage location is invalid.',
                ]);
            }

            if ($lockedItem->storage_location_id === $targetStorageLocationId) {
                throw ValidationException::withMessages([
                    'target_storage_location_id' => 'The target storage location must differ from the current location.',
                ]);
            }

            if ($requestKey !== null) {
                $existingMovement = $this->findIdempotentMovement($lockedItem->id, 'transfer', $requestKey);
                if ($existingMovement instanceof InventoryMovement) {
                    return InventoryItem::query()->findOrFail($lockedItem->id);
                }
            }

            if ($lockedItem->quantity === $quantity) {
                $lockedItem->forceFill(['storage_location_id' => $targetStorageLocationId])->save();
                $targetItem = $lockedItem;
            } else {
                $lockedItem->decrement('quantity', $quantity);

                $targetItem = InventoryItem::query()->firstOrCreate(
                    [
                        'product_id' => $lockedItem->product_id,
                        'storage_location_id' => $targetStorageLocationId,
                        'condition' => $lockedItem->condition,
                        'grading_provider' => $lockedItem->grading_provider,
                        'grade' => $lockedItem->grade,
                    ],
                    [
                        'quantity' => 0,
                        'acquired_at' => $lockedItem->acquired_at,
                        'notes' => $lockedItem->notes,
                    ],
                );

                $targetItem->increment('quantity', $quantity);
            }

            InventoryMovement::query()->create([
                'inventory_item_id' => $lockedItem->id,
                'movement_type' => 'transfer',
                'quantity_delta' => -$quantity,
                'from_storage_location_id' => $sourceLocationId,
                'to_storage_location_id' => $targetStorageLocationId,
                'reason' => $reason,
                'metadata' => [
                    'request_key' => $requestKey,
                    'target_item_id' => $targetItem->id,
                    'transferred_quantity' => $quantity,
                ],
                'occurred_at' => now(),
            ]);

            DB::afterCommit(function () use ($lockedItem, $targetItem, $sourceLocationId, $targetStorageLocationId, $sourceQuantityBefore, $quantity, $reason, $requestKey, $actor): void {
                $this->auditLogger->log(
                    eventType: 'inventory.transfer.executed',
                    auditable: $lockedItem,
                    changes: [
                        'inventory_item_id' => $lockedItem->id,
                        'target_item_id' => $targetItem->id,
                        'request_key' => $requestKey,
                        'quantity' => $quantity,
                        'reason' => $reason,
                        'from_storage_location_id' => $sourceLocationId,
                        'to_storage_location_id' => $targetStorageLocationId,
                        'before' => [
                            'source_quantity' => $sourceQuantityBefore,
                        ],
                        'after' => [
                            'source_quantity' => max(0, $sourceQuantityBefore - $quantity),
                            'target_quantity' => $targetItem->fresh()->quantity,
                        ],
                    ],
                    context: ['source' => 'api.v1.inventory.transfer'],
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
