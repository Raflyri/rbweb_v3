<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreArcadeGameRequest;
use App\Http\Requests\UpdateArcadeGameRequest;
use App\Models\ArcadeGame;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ArcadeGameController extends Controller
{
    /**
     * Display a listing of arcade games.
     */
    public function index(): View
    {
        $games = ArcadeGame::latest()->paginate(15);

        return view('rbdashboard.arcade-games.index', compact('games'));
    }

    /**
     * Show the form for creating a new arcade game.
     */
    public function create(): View
    {
        return view('rbdashboard.arcade-games.create');
    }

    /**
     * Store a newly created arcade game in storage, including thumbnail upload.
     */
    public function store(StoreArcadeGameRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $data['slug'] = ! empty($data['slug'])
            ? Str::slug($data['slug'])
            : ArcadeGame::generateUniqueSlug($data['title']);

        if ($request->hasFile('thumbnail')) {
            $data['thumbnail'] = $request->file('thumbnail')->store('arcade-thumbnails', 'public');
        }

        ArcadeGame::create($data);

        return redirect()
            ->route('arcade-games.index')
            ->with('success', 'Game Arcade berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified arcade game.
     */
    public function edit(ArcadeGame $arcadeGame): View
    {
        return view('rbdashboard.arcade-games.edit', ['game' => $arcadeGame]);
    }

    /**
     * Update the specified arcade game in storage, replacing thumbnail if uploaded.
     */
    public function update(UpdateArcadeGameRequest $request, ArcadeGame $arcadeGame): RedirectResponse
    {
        $data = $request->validated();

        $data['slug'] = ! empty($data['slug'])
            ? Str::slug($data['slug'])
            : ArcadeGame::generateUniqueSlug($data['title'], $arcadeGame->id);

        if ($request->hasFile('thumbnail')) {
            if ($arcadeGame->thumbnail && Storage::disk('public')->exists($arcadeGame->thumbnail)) {
                Storage::disk('public')->delete($arcadeGame->thumbnail);
            }

            $data['thumbnail'] = $request->file('thumbnail')->store('arcade-thumbnails', 'public');
        } else {
            unset($data['thumbnail']);
        }

        $arcadeGame->update($data);

        return redirect()
            ->route('arcade-games.index')
            ->with('success', 'Game Arcade berhasil diperbarui.');
    }

    /**
     * Remove the specified arcade game and its thumbnail from storage.
     */
    public function destroy(ArcadeGame $arcadeGame): RedirectResponse
    {
        if ($arcadeGame->thumbnail && Storage::disk('public')->exists($arcadeGame->thumbnail)) {
            Storage::disk('public')->delete($arcadeGame->thumbnail);
        }

        $arcadeGame->delete();

        return redirect()
            ->route('arcade-games.index')
            ->with('success', 'Game Arcade berhasil dihapus.');
    }
}
