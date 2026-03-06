<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class CreateAuthTokenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'token_name' => ['required', 'string', 'max:255'],
            'abilities' => ['sometimes', 'array', 'min:1', 'max:20'],
            'abilities.*' => ['string', 'distinct', 'max:100'],
            'expires_in_minutes' => ['sometimes', 'integer', 'min:1', 'max:43200'],
        ];
    }
}
