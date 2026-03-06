<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use App\Models\Set;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSetRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Set $set */
        $set = $this->route('set');

        return $this->user()?->can('update', $set) ?? false;
    }

    public function rules(): array
    {
        /** @var Set $set */
        $set = $this->route('set');
        $gameId = (int) ($this->input('game_id') ?? $set->game_id);

        return [
            'game_id' => ['sometimes', 'integer', 'exists:games,id'],
            'name' => ['sometimes', 'string', 'max:140'],
            'code' => ['sometimes', 'string', 'max:40', Rule::unique('sets', 'code')->where('game_id', $gameId)->ignore($set->id)],
            'release_date' => ['sometimes', 'nullable', 'date'],
        ];
    }
}
