<?php

namespace App\Observers;

use App\Models\Article;
use App\Support\ArticleContent;

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
        $this->backfillSummaries($article);
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
