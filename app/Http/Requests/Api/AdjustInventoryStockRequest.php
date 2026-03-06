<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use App\Models\InventoryItem;
use Illuminate\Foundation\Http\FormRequest;

class AdjustInventoryStockRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var InventoryItem $inventoryItem */
        $inventoryItem = $this->route('inventory_item');

        return $this->user()?->can('update', $inventoryItem) ?? false;
    }

    public function rules(): array
    {
        return [
            'quantity_delta' => ['required', 'integer', 'not_in:0'],
            'reason' => ['nullable', 'string', 'max:120'],
            'request_key' => ['nullable', 'string', 'max:120'],
        ];
    }
}
