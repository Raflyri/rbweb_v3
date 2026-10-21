<?php

namespace App\Http\Controllers;

use App\Models\ArcadeGame;
use Illuminate\View\View;

class ArcadeFrontendController extends Controller
{
    /**
     * Display all published ('Tayangkan') arcade games on the Arcade portal.
     */
    public function index(): View
    {
        $games = ArcadeGame::where('status', ArcadeGame::STATUS_TAYANGKAN)
            ->latest()
            ->get();

        return view('arcade.index', compact('games'));
    }

    /**
     * Launch and play a specific published arcade game by slug.
     */
    public function play(string $slug): View
    {
        $game = ArcadeGame::where('slug', $slug)
            ->where('status', ArcadeGame::STATUS_TAYANGKAN)
            ->firstOrFail();

        return view('arcade.play', compact('game'));
    }
}
