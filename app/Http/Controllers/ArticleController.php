<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ArticleController extends Controller
{
    /**
     * Display paginated list of published articles with optional search.
     */
    public function index(Request $request): View
    {
        $locale = app()->getLocale();
        $search = $request->query('search');

        $articles = Article::published()
            ->with(['user', 'tags'])
            ->latest('published_at')
            ->when($search, function ($query) use ($search, $locale) {
                $query->where(function ($q) use ($search, $locale) {
                    $q->where('title->' . $locale, 'like', '%' . $search . '%')
                      ->orWhere('content->' . $locale, 'like', '%' . $search . '%');
                });
            })
            ->paginate(9)
            ->withQueryString();

        return view('blog.index', compact('articles', 'search', 'locale'));
    }

    /**
     * Display a single published article by slug.
     */
    public function show(string $slug): View
    {
        $locale = app()->getLocale();

        $article = Article::published()
            ->whereJsonContains('slug->' . $locale, $slug)
            ->firstOrFail();

        // Get related articles (same status, exclude current, limit 3)
        $related = Article::published()
            ->where('id', '!=', $article->id)
            ->latest('published_at')
            ->take(3)
            ->get();

        return view('blog.show', compact('article', 'related', 'locale'));
    }
}
