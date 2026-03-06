<?php

declare(strict_types=1);

namespace App\Services\Inventory;

use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AdjustInventoryStockService
{
    public function execute(
        InventoryItem $inventoryItem,
        int $quantityDelta,
        ?string $reason = null,
        ?string $requestKey = null,
    ): InventoryItem {
        return DB::transaction(function () use ($inventoryItem, $quantityDelta, $reason, $requestKey): InventoryItem {
            /** @var InventoryItem $lockedItem */
            $lockedItem = InventoryItem::query()->lockForUpdate()->findOrFail($inventoryItem->id);

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
