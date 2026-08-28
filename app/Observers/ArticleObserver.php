<?php

namespace App\Observers;

use App\Models\Article;
use App\Policies\ArticlePolicy;
use App\Observers\Concerns\RefreshesSitemap;
use App\Support\ArticleContent;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Mews\Purifier\Facades\Purifier;

/**
 * Keeps derived Article fields in sync on every save.
 *
 * An article missing an excerpt or meta description still has to render a
 * sensible card on /blog and a sensible <meta> tag for crawlers, so both are
 * back-filled from the body instead of blocking the author on them.
 */
class ArticleObserver
{
    use RefreshesSitemap;

    public function saving(Article $article): void
    {
        $this->guardStatusEscalation($article);
        $this->purifyContent($article);
        $this->pruneBlankLocales($article);
        $this->backfillSummaries($article);
    }

    /**
     * Drop locale entries that carry no real content, for every
     * translatable field except slug (which deliberately holds the same
     * value across all four locales — see Article::boot()).
     *
     * An article can now legitimately be written in a single locale (see
     * ArticleContent::publishBlockers), and several editor UIs — Client
     * Area's Alpine-driven locale tabs in particular — pre-seed every
     * locale's RichEditor so Livewire has something to entangle with.
     * Saving an untouched RichEditor field dehydrates to markup like
     * "<p></p>": present and non-empty as a string, but with no visible
     * text. Left in place, that makes Article::scopeHasContentIn() treat
     * a locale as "written" when nobody wrote anything in it, so an
     * English-only article would start showing up on /blog for Indonesian,
     * Malay, and Japanese visitors too. This runs after purifyContent so
     * blankness is judged on the sanitised value, not the raw one.
     */
    protected function pruneBlankLocales(Article $article): void
    {
        foreach (['title', 'content', 'meta_title', 'meta_description'] as $field) {
            foreach ($article->getTranslations($field) as $locale => $value) {
                if (! $this->isFilled($value)) {
                    // setTranslations($field, $kept) will NOT do — it only
                    // iterates the array it is given, re-reading and
                    // re-merging existing translations per key, so a locale
                    // simply absent from that array is never actually
                    // removed. forgetTranslation() is the method that drops
                    // a key from the stored JSON.
                    $article->forgetTranslation($field, $locale);
                }
            }
        }
    }

    /**
     * Strip dangerous markup from the body before it is stored.
     *
     * blog/show.blade.php renders the body with {!! !!}, so anything that
     * reaches the column is executed in a reader's browser. Sanitising on the
     * way in means every existing article is cleaned the next time it is
     * saved, and the RichEditor's own formatting survives (see the 'article'
     * profile in config/purifier.php).
     */
    protected function purifyContent(Article $article): void
    {
        if (! $article->isDirty('content')) {
            return;
        }

        foreach ($article->getTranslations('content') as $locale => $html) {
            if (! is_string($html) || $html === '') {
                continue;
            }

            $article->setTranslation('content', $locale, Purifier::clean($html, 'article'));
        }
    }

    /**
     * Last line of defence against self-publishing.
     *
     * The Filament forms already hide Published/Scheduled from clients, but UI
     * options are trivially bypassed by a crafted Livewire request, so the
     * escalation is refused at the model layer too.
     *
     * Unauthenticated saves (console commands, seeders, factories) are allowed
     * through — there is no user to authorise and those paths are trusted.
     */
    protected function guardStatusEscalation(Article $article): void
    {
        if (! $article->isDirty('status')) {
            return;
        }

        if (! in_array($article->status, ArticlePolicy::PUBLIC_STATUSES, true)) {
            return;
        }

        $user = Auth::user();

        if ($user === null || ArticlePolicy::userCanPublish($user)) {
            return;
        }

        throw new AuthorizationException(
            'Anda tidak berwenang menerbitkan artikel. Kirim artikel ini sebagai "Pending Review" agar ditinjau admin terlebih dahulu.'
        );
    }

    /**
     * Fill excerpt / meta_description from the body for every locale that has
     * content but no summary. Author-written values are never overwritten.
     */
    public function saved(Article $article): void
    {
        // Only a change that alters what the public can reach matters here:
        // an article going live or dark, or its permalink moving.
        if ($this->isPubliclyVisible($article)
            || $article->wasChanged('status')
            || $article->wasChanged('slug')) {
            $this->refreshSitemapSoon();
        }
    }

    public function deleted(Article $article): void
    {
        if ($this->isPubliclyVisible($article)) {
            $this->refreshSitemapSoon();
        }
    }

    protected function isPubliclyVisible(Article $article): bool
    {
        return in_array($article->status, ArticlePolicy::PUBLIC_STATUSES, true);
    }

    protected function backfillSummaries(Article $article): void
    {
        $contents = $article->getTranslations('content');

        if ($contents === []) {
            return;
        }

        $excerpts = $article->getTranslations('excerpt');
        $metas    = $article->getTranslations('meta_description');

        foreach ($contents as $locale => $html) {
            if (! is_string($html) || trim(strip_tags($html)) === '') {
                continue;
            }

            if (! $this->isFilled($excerpts[$locale] ?? null)) {
                $summary = ArticleContent::excerptFrom($html, 200);

                if ($summary !== '') {
                    $article->setTranslation('excerpt', $locale, $summary);
                }
            }

            if (! $this->isFilled($metas[$locale] ?? null)) {
                $summary = ArticleContent::excerptFrom($html, 155);

                if ($summary !== '') {
                    $article->setTranslation('meta_description', $locale, $summary);
                }
            }
        }
    }

    protected function isFilled(mixed $value): bool
    {
        return is_string($value) && trim(strip_tags($value)) !== '';
    }
}
