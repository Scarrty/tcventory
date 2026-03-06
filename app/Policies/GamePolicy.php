<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Game;
use App\Models\User;

class GamePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('catalog.view');
    }

    public function view(User $user, Game $game): bool
    {
        return $user->hasPermissionTo('catalog.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('catalog.create');
    }

    public function update(User $user, Game $game): bool
    {
        return $user->hasPermissionTo('catalog.update');
    }

    public function delete(User $user, Game $game): bool
    {
        return $user->hasPermissionTo('catalog.delete');
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasPermissionTo('catalog.delete');
    }
}
