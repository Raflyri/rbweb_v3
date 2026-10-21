<?php

namespace App\Http\Requests;

use App\Models\ArcadeGame;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateArcadeGameRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $game = $this->route('arcade_game') ?? $this->route('arcadeGame');
        $gameId = $game instanceof ArcadeGame ? $game->id : $game;

        return [
            'title'       => ['required', 'string', 'max:255'],
            'slug'        => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('arcade_games', 'slug')->ignore($gameId),
            ],
            'description' => ['required', 'string'],
            'thumbnail'   => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'game_url'    => ['required', 'url', 'max:500'],
            'status'      => ['required', Rule::in(ArcadeGame::STATUSES)],
        ];
    }
}
