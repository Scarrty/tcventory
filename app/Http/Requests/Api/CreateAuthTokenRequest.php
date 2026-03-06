<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateAuthTokenRequest extends FormRequest
{
    /**
     * @var list<string>
     */
    private const ALLOWED_ABILITIES = [
        'inventory:read',
        'inventory:write',
        'catalog:read',
        'catalog:write',
        'reports:read',
    ];

    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $abilities = $this->input('abilities');

        if (! is_array($abilities)) {
            return;
        }

        $normalized = array_values(array_unique(array_map(
            static fn (mixed $ability): string => mb_strtolower(trim((string) $ability)),
            $abilities,
        )));

        $this->merge([
            'abilities' => $normalized,
        ]);
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'token_name' => ['required', 'string', 'max:255'],
            'abilities' => ['sometimes', 'array', 'min:1', 'max:20'],
            'abilities.*' => ['string', 'distinct:strict', 'max:100', Rule::in(self::ALLOWED_ABILITIES)],
            'expires_in_minutes' => ['sometimes', 'integer', 'min:1', 'max:43200'],
        ];
    }
}
