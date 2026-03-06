<?php

declare(strict_types=1);

namespace App\Services\Catalog;

use App\Models\Product;
use Illuminate\Validation\ValidationException;

class DeleteProductService
{
    public function execute(Product $product): void
    {
        if ($product->inventoryItems()->exists()) {
            throw ValidationException::withMessages([
                'product' => 'The product cannot be deleted because inventory items are still assigned to it.',
            ]);
        }

        $product->delete();
    }
}
