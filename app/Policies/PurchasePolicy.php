<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Purchase;
use App\Models\User;

class PurchasePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('finance.view');
    }

    public function view(User $user, Purchase $purchase): bool
    {
        return $user->hasPermissionTo('finance.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('finance.create');
    }

    public function update(User $user, Purchase $purchase): bool
    {
        return $user->hasPermissionTo('finance.update');
    }

    public function delete(User $user, Purchase $purchase): bool
    {
        return $user->hasPermissionTo('finance.delete');
    }
}
