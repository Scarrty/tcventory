<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Set;
use App\Models\User;

class SetPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('catalog.view');
    }

    public function view(User $user, Set $set): bool
    {
        return $user->hasPermissionTo('catalog.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('catalog.create');
    }

    public function update(User $user, Set $set): bool
    {
        return $user->hasPermissionTo('catalog.update');
    }

    public function delete(User $user, Set $set): bool
    {
        return $user->hasPermissionTo('catalog.delete');
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasPermissionTo('catalog.delete');
    }

    public function restore(User $user, Set $set): bool
    {
        return $user->hasPermissionTo('catalog.update');
    }

    public function forceDelete(User $user, Set $set): bool
    {
        return $user->hasPermissionTo('catalog.delete');
    }
}
