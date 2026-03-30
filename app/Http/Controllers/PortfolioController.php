<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Profile;
use Illuminate\Support\Facades\Cache;

class PortfolioController extends Controller
{
    public function show(string $slug)
    {
        // Cache the profile + all relations for 5 minutes per unique slug.
        // The cache key is bust-able from the Profile model observer if needed.
        $profile = Cache::remember("portfolio:{$slug}", 300, function () use ($slug) {
            return Profile::whereNotNull('custom_url_slug')
                ->where('custom_url_slug', $slug)
                ->with([
                    'user.experiences' => fn ($q) => $q->orderByDesc('start_date'),
                    'user.education'   => fn ($q) => $q->orderByDesc('start_date'),
                    'user.skills',
                    'user.achievements' => fn ($q) => $q->orderByDesc('date'),
                    'user.posts' => fn ($q) => $q
                        ->where('status', Post::STATUS_PUBLISHED)
                        ->orderByDesc('published_at')
                        ->limit(6),
                ])
                ->firstOrFail();
        });

        return view('portfolio.show', compact('profile'));
    }
}
