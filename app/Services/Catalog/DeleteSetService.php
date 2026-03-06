<?php

declare(strict_types=1);

namespace App\Services\Catalog;

use App\Models\Set;
use Illuminate\Validation\ValidationException;

class DeleteSetService
{
    public function execute(Set $set): void
    {
        if ($set->products()->exists()) {
            throw ValidationException::withMessages([
                'set' => 'The set cannot be deleted because products are still assigned to it.',
            ]);
        }

        $set->delete();
    }
}
