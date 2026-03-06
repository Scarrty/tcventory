<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Product $product */
        $product = $this->route('product');

        return $this->user()?->can('update', $product) ?? false;
    }

    public function rules(): array
    {
        /** @var Product $product */
        $product = $this->route('product');

        return [
            'game_id' => ['sometimes', 'integer', 'exists:games,id'],
            'set_id' => ['sometimes', 'nullable', 'integer', 'exists:sets,id'],
            'name' => ['sometimes', 'string', 'max:160'],
            'sku' => ['sometimes', 'nullable', 'string', 'max:80', Rule::unique('products', 'sku')->ignore($product->id)],
            'product_type' => ['sometimes', 'string', 'max:40'],
            'rarity' => ['sometimes', 'nullable', 'string', 'max:40'],
            'is_sealed' => ['sometimes', 'boolean'],
        ];
    }
}
