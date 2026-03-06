<?php

declare(strict_types=1);

namespace App\Services\Catalog;

use App\Models\Game;
use Illuminate\Validation\ValidationException;

class DeleteGameService
{
    public function execute(Game $game): void
    {
        if ($game->sets()->exists()) {
            throw ValidationException::withMessages([
                'game' => 'The game cannot be deleted because sets are still assigned to it.',
            ]);
        }

        if ($game->products()->exists()) {
            throw ValidationException::withMessages([
                'game' => 'The game cannot be deleted because products are still assigned to it.',
            ]);
        }

        $game->delete();
    }
}
