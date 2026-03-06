<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', \App\Models\Set::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'game_id' => ['required', 'integer', 'exists:games,id'],
            'name' => ['required', 'string', 'max:140'],
            'code' => ['required', 'string', 'max:40', Rule::unique('sets', 'code')->where('game_id', (int) $this->input('game_id'))],
            'release_date' => ['nullable', 'date'],
        ];
    }
}
