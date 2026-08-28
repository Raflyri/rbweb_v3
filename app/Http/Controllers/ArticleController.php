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
            // An article only has to be written in one locale to publish, and
            // only shows up on the /blog of the locale(s) it actually has —
            // no falling back to another language's text here.
            ->hasContentIn($locale)
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

        // hasContentIn($locale) here means an article with no content in the
        // current locale 404s even if its slug happens to match under a
        // legacy key — visiting it in a language it was never written in is
        // not a valid URL, not a redirect target.
        $article = Article::visiblePublic()
            ->hasContentIn($locale)
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
            ->hasContentIn($locale)
            ->with(['user', 'tags'])
            ->where('id', '!=', $article->id)
            ->latest('published_at')
            ->take(3)
            ->get();

        return view('blog.show', compact('article', 'related', 'locale'));
    }
}
