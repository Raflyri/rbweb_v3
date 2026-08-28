<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Shared text-quality helpers for Article content.
 *
 * Both the Filament forms (publish guardrails) and the
 * articles:cleanup-test-data command reason about "how much real content
 * does this article actually have?", so the logic lives in one place to
 * keep the two from drifting apart.
 */
class ArticleContent
{
    /** Minimum word count required before an article may be published. */
    public const MIN_PUBLISH_WORDS = 50;

    /** At or below this word count an article is considered throwaway test data. */
    public const TEST_DATA_MAX_WORDS = 10;

    /**
     * Count words in an HTML fragment.
     *
     * str_word_count() returns 0 for Japanese/Chinese text because those
     * scripts are not space separated, which would wrongly flag a perfectly
     * good JA article as empty. CJK codepoints are therefore counted
     * individually and the remaining text is counted by whitespace groups.
     */
    public static function wordCount(?string $html): int
    {
        $text = html_entity_decode(strip_tags((string) $html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = trim(preg_replace('/\s+/u', ' ', $text) ?? '');

        if ($text === '') {
            return 0;
        }

        $cjkPattern = '/[\x{4E00}-\x{9FFF}\x{3400}-\x{4DBF}\x{3040}-\x{30FF}\x{AC00}-\x{D7AF}]/u';

        $cjk   = preg_match_all($cjkPattern, $text) ?: 0;
        $rest  = preg_replace($cjkPattern, ' ', $text) ?? '';
        $latin = preg_match_all('/[\p{L}\p{N}]+/u', $rest) ?: 0;

        return $cjk + $latin;
    }

    /**
     * Highest word count found across the given locales of a translatable value.
     *
     * @param  mixed         $value   Raw translatable state: string, or [locale => html].
     * @param  array|null    $locales Restrict to these locales; null checks every locale.
     */
    public static function bestWordCount(mixed $value, ?array $locales = null): int
    {
        if (is_string($value) || $value === null) {
            return static::wordCount($value);
        }

        if (! is_array($value)) {
            return 0;
        }

        $best = 0;

        foreach ($value as $locale => $html) {
            if ($locales !== null && ! in_array($locale, $locales, true)) {
                continue;
            }

            if (! is_string($html)) {
                continue;
            }

            $best = max($best, static::wordCount($html));
        }

        return $best;
    }

    /**
     * Does a translatable value contain a URL where prose was expected?
     * A title like "https://rbeverything.com/blog/article" is the signature
     * of a smoke-test row rather than a real article.
     */
    public static function looksLikeUrl(mixed $value): bool
    {
        foreach (static::flatten($value) as $text) {
            if (Str::contains(Str::lower($text), ['http://', 'https://'])) {
                return true;
            }
        }

        return false;
    }

    /** Is any locale of a translatable value filled in? */
    public static function hasAnyTranslation(mixed $value): bool
    {
        foreach (static::flatten($value) as $text) {
            if (trim(strip_tags($text)) !== '') {
                return true;
            }
        }

        return false;
    }

    /**
     * The first non-empty value across the given locales, tried in order.
     *
     * Used anywhere the UI needs to show "a" title/summary for an article
     * regardless of which single language it happens to be written in —
     * e.g. the admin articles list, where an ms-only or ja-only article
     * should still show something instead of a blank cell.
     *
     * @param  array<string, mixed>|mixed $value
     * @param  array<int, string>|null    $locales Defaults to ArticleLocale::SUPPORTED.
     */
    public static function bestTranslation(mixed $value, ?array $locales = null): ?string
    {
        if (! is_array($value)) {
            return null;
        }

        foreach ($locales ?? ArticleLocale::SUPPORTED as $locale) {
            $text = $value[$locale] ?? null;

            if (is_string($text) && trim(strip_tags($text)) !== '') {
                return $text;
            }
        }

        return null;
    }

    /**
     * Is a Filament FileUpload / model attribute holding an actual file?
     * FileUpload state is an array keyed by upload UUID while editing and a
     * plain path string once saved, so both shapes have to be accepted.
     */
    public static function hasThumbnail(mixed $value): bool
    {
        if (is_array($value)) {
            return count(array_filter($value)) > 0;
        }

        return is_string($value) && trim($value) !== '';
    }

    /** Build a plain-text excerpt from an HTML body. */
    public static function excerptFrom(?string $html, int $limit = 160): string
    {
        $text = html_entity_decode(strip_tags((string) $html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = trim(preg_replace('/\s+/u', ' ', $text) ?? '');

        return $text === '' ? '' : Str::limit($text, $limit);
    }

    /**
     * Reasons why the given form state may NOT be published yet.
     * An empty array means the article is cleared for publishing.
     *
     * @param  array $state Raw Filament form state (title/content/thumbnail keys).
     * @return array<int, string>
     */
    public static function publishBlockers(array $state): array
    {
        $blockers = [];

        // Any one of the four supported locales is enough — an article does
        // not have to be translated everywhere before it can go live, it
        // just needs real content in at least one language.
        $words = static::bestWordCount($state['content'] ?? null, ArticleLocale::SUPPORTED);

        if ($words < static::MIN_PUBLISH_WORDS) {
            $blockers[] = sprintf(
                'Konten belum layak terbit: minimal %d kata pada salah satu bahasa (EN/ID/MS/JA), saat ini %d kata.',
                static::MIN_PUBLISH_WORDS,
                $words,
            );
        }

        if (! static::hasThumbnail($state['thumbnail'] ?? null)) {
            $blockers[] = 'Thumbnail wajib diisi sebelum artikel dipublikasikan.';
        }

        return $blockers;
    }

    /**
     * Soft warnings — things that will be auto-filled on save but that the
     * author probably wants to write themselves.
     *
     * @return array<int, string>
     */
    public static function publishWarnings(array $state): array
    {
        $warnings = [];

        if (! static::hasAnyTranslation($state['excerpt'] ?? null)) {
            $warnings[] = 'Excerpt masih kosong — akan dibuat otomatis dari konten saat disimpan.';
        }

        if (! static::hasAnyTranslation($state['meta_description'] ?? null)) {
            $warnings[] = 'Meta description masih kosong — akan dibuat otomatis dari konten saat disimpan.';
        }

        return $warnings;
    }

    /**
     * Normalise a translatable value into a flat list of strings.
     *
     * @return array<int, string>
     */
    protected static function flatten(mixed $value): array
    {
        if (is_string($value)) {
            return [$value];
        }

        if (! is_array($value)) {
            return [];
        }

        return array_values(array_filter($value, 'is_string'));
    }
}
