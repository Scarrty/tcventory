<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\Valuation;

class ValuationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('finance.view');
    }

    public function view(User $user, Valuation $valuation): bool
    {
        return $user->hasPermissionTo('finance.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('finance.create');
    }

    public function update(User $user, Valuation $valuation): bool
    {
        return $user->hasPermissionTo('finance.update');
    }

    public function delete(User $user, Valuation $valuation): bool
    {
        return $user->hasPermissionTo('finance.delete');
    }
}
