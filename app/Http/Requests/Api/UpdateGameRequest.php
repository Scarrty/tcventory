<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use App\Models\Game;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateGameRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Game $game */
        $game = $this->route('game');

        return $this->user()?->can('update', $game) ?? false;
    }

    public function rules(): array
    {
        /** @var Game $game */
        $game = $this->route('game');

        return [
            'name' => ['sometimes', 'string', 'max:120'],
            'slug' => ['sometimes', 'string', 'max:140', Rule::unique('games', 'slug')->ignore($game->id)],
            'publisher' => ['sometimes', 'nullable', 'string', 'max:120'],
        ];
    }
}
