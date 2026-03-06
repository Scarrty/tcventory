<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use App\Models\Valuation;
use Illuminate\Foundation\Http\FormRequest;

class StoreValuationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Valuation::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'inventory_item_id' => ['required', 'integer', 'exists:inventory_items,id'],
            'value_amount' => ['required', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'size:3'],
            'source' => ['nullable', 'string', 'max:60'],
            'valued_at' => ['required', 'date'],
        ];
    }
}
