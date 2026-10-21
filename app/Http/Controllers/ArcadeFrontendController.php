<?php

namespace App\Http\Controllers;

use App\Models\ArcadeGame;
use App\Models\GameCategory;
use Illuminate\View\View;

class ArcadeFrontendController extends Controller
{
    /**
     * Display all published ('Tayangkan') arcade games on the Arcade portal.
     */
    public function index(): View
    {
        $games = ArcadeGame::with('category')
            ->where('status', ArcadeGame::STATUS_TAYANGKAN)
            ->latest()
            ->get();

        $categories = GameCategory::withCount(['games' => function ($query) {
            $query->where('status', ArcadeGame::STATUS_TAYANGKAN);
        }])->get();

        return view('arcade.index', compact('games', 'categories'));
    }

    /**
     * Launch and play a specific published arcade game by slug.
     */
    public function play(string $slug): View
    {
        $game = ArcadeGame::with('category')
            ->where('slug', $slug)
            ->where('status', ArcadeGame::STATUS_TAYANGKAN)
            ->firstOrFail();

        return view('arcade.play', compact('game'));
    }
}
