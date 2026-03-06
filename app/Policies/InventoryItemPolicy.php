<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\InventoryItem;
use App\Models\User;

class InventoryItemPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('inventory.view');
    }

    public function view(User $user, InventoryItem $inventoryItem): bool
    {
        return $user->hasPermissionTo('inventory.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('inventory.create');
    }

    public function update(User $user, InventoryItem $inventoryItem): bool
    {
        return $user->hasPermissionTo('inventory.update');
    }

    public function delete(User $user, InventoryItem $inventoryItem): bool
    {
        return $user->hasPermissionTo('inventory.delete');
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasPermissionTo('inventory.delete');
    }

    public function restore(User $user, InventoryItem $inventoryItem): bool
    {
        return $user->hasPermissionTo('inventory.update');
    }

    public function forceDelete(User $user, InventoryItem $inventoryItem): bool
    {
        return $user->hasPermissionTo('inventory.delete');
    }
}
