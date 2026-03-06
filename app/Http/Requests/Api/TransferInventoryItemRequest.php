<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use App\Models\InventoryItem;
use Illuminate\Foundation\Http\FormRequest;

class TransferInventoryItemRequest extends FormRequest
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
            'quantity' => ['required', 'integer', 'min:1'],
            'target_storage_location_id' => ['required', 'integer', 'exists:storage_locations,id'],
            'reason' => ['nullable', 'string', 'max:120'],
            'request_key' => ['nullable', 'string', 'max:120'],
        ];
    }
}
