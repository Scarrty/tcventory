<?php

declare(strict_types=1);

namespace App\Services\Inventory;

use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use App\Models\StorageLocation;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TransferInventoryItemService
{
    public function execute(
        InventoryItem $inventoryItem,
        int $quantity,
        int $targetStorageLocationId,
        ?string $reason = null,
        ?string $requestKey = null,
    ): InventoryItem {
        return DB::transaction(function () use ($inventoryItem, $quantity, $targetStorageLocationId, $reason, $requestKey): InventoryItem {
            /** @var InventoryItem $lockedItem */
            $lockedItem = InventoryItem::query()->lockForUpdate()->findOrFail($inventoryItem->id);

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
                'from_storage_location_id' => $inventoryItem->storage_location_id,
                'to_storage_location_id' => $targetStorageLocationId,
                'reason' => $reason,
                'metadata' => [
                    'request_key' => $requestKey,
                    'target_item_id' => $targetItem->id,
                    'transferred_quantity' => $quantity,
                ],
                'occurred_at' => now(),
            ]);

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
