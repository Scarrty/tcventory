<?php

declare(strict_types=1);

namespace App\Services\Inventory;

use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DeleteInventoryItemService
{
    public function execute(InventoryItem $inventoryItem): void
    {
        DB::transaction(function () use ($inventoryItem): void {
            /** @var InventoryItem $lockedItem */
            $lockedItem = InventoryItem::query()->lockForUpdate()->findOrFail($inventoryItem->id);

            if ($lockedItem->inventoryMovements()->exists()) {
                throw ValidationException::withMessages([
                    'inventory_item' => 'The inventory item cannot be deleted because movements already exist for it.',
                ]);
            }

            InventoryMovement::query()->create([
                'inventory_item_id' => $lockedItem->id,
                'movement_type' => 'deletion',
                'quantity_delta' => -$lockedItem->quantity,
                'from_storage_location_id' => $lockedItem->storage_location_id,
                'to_storage_location_id' => null,
                'reason' => 'Soft delete inventory item',
                'metadata' => [
                    'deleted_inventory_item_id' => $lockedItem->id,
                    'archived_quantity' => $lockedItem->quantity,
                ],
                'occurred_at' => now(),
            ]);

            $lockedItem->delete();
        });
    }
}
