<?php

namespace App\Observers;

use App\Models\Product;
use App\Observers\Concerns\RefreshesSitemap;
use Illuminate\Support\Str;
use Mews\Purifier\Facades\Purifier;

/**
 * Keeps derived and untrusted Product fields safe and in sync on every save.
 *
 * Sanitising happens here, on the way IN, exactly as ArticleObserver does it —
 * not in the Blade view. A view-side Purifier::clean() call is one forgotten
 * {!! !!} away from an XSS hole; cleaning on save means whatever sits in the
 * column is already safe no matter which template renders it later.
 */
class ProductObserver
{
    use RefreshesSitemap;

    public function saving(Product $product): void
    {
        $this->purifyDescription($product);
        $this->pruneBlankLocales($product);
        $this->backfillMetaDescription($product);
    }

    /**
     * Strip dangerous markup from the rich-text description before storing it.
     *
     * See the 'product' profile in config/purifier.php for what survives —
     * it covers every button on the RichEditor toolbar used by the form.
     */
    protected function purifyDescription(Product $product): void
    {
        if (! $product->isDirty('description')) {
            return;
        }

        foreach ($product->getTranslations('description') as $locale => $html) {
            if (! is_string($html) || $html === '') {
                continue;
            }

            $product->setTranslation('description', $locale, Purifier::clean($html, 'product'));
        }
    }

    /**
     * Drop locale entries that carry no real content.
     *
     * An untouched RichEditor dehydrates to markup like "<p></p>": non-empty as
     * a string, but with nothing in it. Left in place, Product::translate()
     * would happily return that empty paragraph for a locale nobody wrote,
     * instead of falling back to the locale that actually has copy.
     */
    protected function pruneBlankLocales(Product $product): void
    {
        foreach ($product->translatable as $field) {
            foreach ($product->getTranslations($field) as $locale => $value) {
                if (! $this->isFilled($value)) {
                    // forgetTranslation(), not setTranslations(): the latter
                    // merges rather than removes, so a key simply left out of
                    // the array survives in the stored JSON.
                    $product->forgetTranslation($field, $locale);
                }
            }
        }
    }

    /**
     * Fill meta_description per locale from the short description, then the
     * body. An admin-written value is never overwritten.
     */
    protected function backfillMetaDescription(Product $product): void
    {
        $metas = $product->getTranslations('meta_description');

        foreach (Product::LOCALES as $locale) {
            if ($this->isFilled($metas[$locale] ?? null)) {
                continue;
            }

            $source = $product->getTranslation('short_description', $locale, false)
                ?: $product->getTranslation('description', $locale, false);

            if (! is_string($source) || trim(strip_tags($source)) === '') {
                continue;
            }

            $summary = Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags($source))), 155);

            if ($summary !== '') {
                $product->setTranslation('meta_description', $locale, $summary);
            }
        }
    }

    public function saved(Product $product): void
    {
        // Only changes that alter what the public can reach are worth a rebuild:
        // an item appearing or disappearing from /produk-layanan, or its
        // permalink moving.
        if ($product->is_active || $product->wasChanged('is_active') || $product->wasChanged('slug')) {
            $this->refreshSitemapSoon();
        }
    }

    public function deleted(Product $product): void
    {
        if ($product->is_active) {
            $this->refreshSitemapSoon();
        }
    }

    protected function isFilled(mixed $value): bool
    {
        return is_string($value) && trim(strip_tags($value)) !== '';
    }
}
