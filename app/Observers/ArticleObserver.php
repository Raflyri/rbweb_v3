<?php

namespace App\Observers;

use App\Models\Article;
use App\Policies\ArticlePolicy;
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
    public function saving(Article $article): void
    {
        $this->guardStatusEscalation($article);
        $this->purifyContent($article);
        $this->backfillSummaries($article);
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
