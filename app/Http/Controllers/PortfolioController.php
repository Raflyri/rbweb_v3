<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use Illuminate\Http\Request;

class PortfolioController extends Controller
{
    public function show($slug)
    {
        $profile = Profile::where('custom_url_slug', $slug)
            ->with(['user.experiences', 'user.education', 'user.skills', 'user.achievements', 'user.posts'])
            ->firstOrFail();

        return view('portfolio.show', compact('profile'));
    }
}
