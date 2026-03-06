<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', \App\Models\Product::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'game_id' => ['required', 'integer', 'exists:games,id'],
            'set_id' => ['nullable', 'integer', 'exists:sets,id'],
            'name' => ['required', 'string', 'max:160'],
            'sku' => ['nullable', 'string', 'max:80', Rule::unique('products', 'sku')],
            'product_type' => ['required', 'string', 'max:40'],
            'rarity' => ['nullable', 'string', 'max:40'],
            'is_sealed' => ['sometimes', 'boolean'],
        ];
    }
}
