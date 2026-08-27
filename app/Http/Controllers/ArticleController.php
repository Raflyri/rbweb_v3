<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Support\ArticleLocale;
use App\Support\ArticleSearch;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ArticleController extends Controller
{
    /**
     * Display paginated list of published articles with optional search.
     *
     * visiblePublic() rather than published(): on this shared host the
     * scheduler may lag, and an article whose publish time has passed should
     * not stay hidden just because app:publish-scheduled-articles has not run
     * yet. The command still normalises `status` behind the scenes.
     */
    public function index(Request $request): View
    {
        $locale = ArticleLocale::current();
        $search = $request->query('search');

        $articles = Article::visiblePublic()
            ->with(['user', 'tags'])
            // Relevance ordering is applied by ArticleSearch when full text is
            // used, so recency is only the tie-breaker on a search.
            ->when(! $search, fn ($query) => $query->latest('published_at'))
            ->when($search, fn ($query) => ArticleSearch::apply($query, $search, $locale))
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
        $locale = ArticleLocale::current();

        $article = Article::visiblePublic()
            ->with(['user', 'tags'])
            ->where(function ($query) use ($slug, $locale) {
                // Primary: match the active locale key inside the JSON column.
                $query->where("slug->{$locale}", $slug);

                // Fallback: if no match in the active locale, search every
                // stored locale key so old links keep working after locale changes.
                foreach (ArticleLocale::lookupKeys() as $loc) {
                    if ($loc !== $locale) {
                        $query->orWhere("slug->{$loc}", $slug);
                    }
                }
            })
            ->firstOrFail();

        // Related articles — exclude current, newest first
        $related = Article::visiblePublic()
            ->with(['user', 'tags'])
            ->where('id', '!=', $article->id)
            ->latest('published_at')
            ->take(3)
            ->get();

        return view('blog.show', compact('article', 'related', 'locale'));
    }
}
