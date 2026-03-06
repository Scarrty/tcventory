<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('catalog.view');
    }

    public function view(User $user, Product $product): bool
    {
        return $user->hasPermissionTo('catalog.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('catalog.create');
    }

    public function update(User $user, Product $product): bool
    {
        return $user->hasPermissionTo('catalog.update');
    }

    public function delete(User $user, Product $product): bool
    {
        return $user->hasPermissionTo('catalog.delete');
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasPermissionTo('catalog.delete');
    }
}
