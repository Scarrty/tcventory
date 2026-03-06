<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use App\Models\InventoryItem;
use Illuminate\Foundation\Http\FormRequest;

class UpdateInventoryItemRequest extends FormRequest
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
            'product_id' => ['sometimes', 'integer', 'exists:products,id'],
            'storage_location_id' => ['sometimes', 'nullable', 'integer', 'exists:storage_locations,id'],
            'quantity' => ['sometimes', 'integer', 'min:1'],
            'condition' => ['sometimes', 'string', 'max:40'],
            'grading_provider' => ['sometimes', 'nullable', 'string', 'max:80'],
            'grade' => ['sometimes', 'nullable', 'string', 'max:40'],
            'acquired_at' => ['sometimes', 'nullable', 'date'],
            'notes' => ['sometimes', 'nullable', 'string', 'max:500'],
        ];
    }
}
