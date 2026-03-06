<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\StorageLocation;
use App\Models\User;

class StorageLocationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('inventory.view');
    }

    public function view(User $user, StorageLocation $storageLocation): bool
    {
        return $user->hasPermissionTo('inventory.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('inventory.create');
    }

    public function update(User $user, StorageLocation $storageLocation): bool
    {
        return $user->hasPermissionTo('inventory.update');
    }

    public function delete(User $user, StorageLocation $storageLocation): bool
    {
        return $user->hasPermissionTo('inventory.delete');
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasPermissionTo('inventory.delete');
    }
}
