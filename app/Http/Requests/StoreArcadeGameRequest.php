<?php

namespace App\Http\Requests;

use App\Models\ArcadeGame;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreArcadeGameRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'title'       => ['required', 'string', 'max:255'],
            'slug'        => ['nullable', 'string', 'max:255', 'unique:arcade_games,slug'],
            'description' => ['required', 'string'],
            'thumbnail'   => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'game_url'    => ['required', 'url', 'max:500'],
            'status'      => ['required', Rule::in(ArcadeGame::STATUSES)],
        ];
    }
}
