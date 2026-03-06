<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Sale;
use App\Models\User;

class SalePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('finance.view');
    }

    public function view(User $user, Sale $sale): bool
    {
        return $user->hasPermissionTo('finance.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('finance.create');
    }

    public function update(User $user, Sale $sale): bool
    {
        return $user->hasPermissionTo('finance.update');
    }

    public function delete(User $user, Sale $sale): bool
    {
        return $user->hasPermissionTo('finance.delete');
    }
}
