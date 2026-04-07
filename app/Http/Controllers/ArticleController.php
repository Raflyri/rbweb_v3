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
     *
     * The slug column is a Spatie Translatable JSON field stored as
     * {"id":"my-slug","en":"my-slug", ...}.  MySQL's arrow operator
     * (slug->"$.en") is the only reliable way to query inside the JSON.
     * whereJsonContains('slug->locale', $value) is NOT valid for a scalar
     * string comparison — it requires an array value on MySQL 8.
     */
    public function show(string $slug): View
    {
        $locale = app()->getLocale();

        $article = Article::published()
            ->with(['user', 'tags'])
            ->where(function ($query) use ($slug, $locale) {
                // Primary: match the active locale key inside the JSON column.
                $query->where("slug->{$locale}", $slug);

                // Fallback: if no match in the active locale, search every
                // stored locale key so old links keep working after locale changes.
                foreach (['id', 'my', 'en', 'jp', 'ms', 'ja'] as $loc) {
                    if ($loc !== $locale) {
                        $query->orWhere("slug->{$loc}", $slug);
                    }
                }
            })
            ->firstOrFail();

        // Related articles — exclude current, newest first
        $related = Article::published()
            ->with(['user', 'tags'])
            ->where('id', '!=', $article->id)
            ->latest('published_at')
            ->take(3)
            ->get();

        return view('blog.show', compact('article', 'related', 'locale'));
    }
}
