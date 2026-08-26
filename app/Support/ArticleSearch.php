<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;

/**
 * Search across article titles and bodies.
 *
 * The articles table carries STORED generated columns per locale plus a
 * FULLTEXT index over the Latin-script ones (en/id/ms). Those existed since
 * April but nothing used them: the controller ran
 * `where('title->'.$locale, 'like', '%term%')`, and a leading wildcard cannot
 * use any index at all, so every search was a full table scan.
 */
class ArticleSearch
{
    /** Locales whose generated columns are covered by articles_fulltext_index. */
    public const FULLTEXT_LOCALES = ['en', 'id', 'ms'];

    /**
     * Locales that have generated columns but no usable full-text index.
     * Japanese is not whitespace delimited, so the default parser cannot
     * tokenise it; see the migration for why ngram is not an option here.
     */
    public const LIKE_ONLY_LOCALES = ['ja'];

    /** InnoDB ignores tokens shorter than innodb_ft_min_token_size (3 by default). */
    public const MIN_TOKEN_LENGTH = 3;

    /**
     * Apply a search to the query, ordered by relevance where possible.
     */
    public static function apply(Builder $query, string $search, string $locale): Builder
    {
        $locale = ArticleLocale::normalize($locale);
        $terms  = static::booleanTerms($search);

        if ($terms !== null && in_array($locale, static::FULLTEXT_LOCALES, true)) {
            return static::applyFullText($query, $terms, $locale);
        }

        return static::applyLike($query, $search, $locale);
    }

    /**
     * MATCH … AGAINST in boolean mode, with a trailing wildcard on each term
     * so "lara" still finds "Laravel" the way the old LIKE '%…%' did.
     */
    protected static function applyFullText(Builder $query, string $terms, string $locale): Builder
    {
        $columns = ["title_{$locale}", "content_{$locale}"];
        $match   = 'MATCH (' . implode(', ', $columns) . ') AGAINST (? IN BOOLEAN MODE)';

        return $query
            ->whereRaw($match, [$terms])
            ->orderByRaw("{$match} DESC", [$terms]);
    }

    /**
     * Fallback for Japanese, for terms too short to be indexed, and for any
     * locale without a generated column. Reads the stored generated column
     * when one exists — still cheaper than extracting from JSON per row.
     */
    protected static function applyLike(Builder $query, string $search, string $locale): Builder
    {
        $hasGeneratedColumns = in_array($locale, static::FULLTEXT_LOCALES, true)
            || in_array($locale, static::LIKE_ONLY_LOCALES, true);

        $titleColumn   = $hasGeneratedColumns ? "title_{$locale}"   : "title->{$locale}";
        $contentColumn = $hasGeneratedColumns ? "content_{$locale}" : "content->{$locale}";

        $like = '%' . addcslashes($search, '\\%_') . '%';

        return $query->where(function (Builder $q) use ($titleColumn, $contentColumn, $like) {
            $q->where($titleColumn, 'like', $like)
              ->orWhere($contentColumn, 'like', $like);
        });
    }

    /**
     * Turn user input into a safe boolean-mode expression, or null when
     * full-text search cannot serve this query and LIKE should be used.
     *
     * Boolean operators (+ - > < ( ) ~ * " @) are stripped rather than
     * escaped: users type them by accident, and a stray one is a syntax error.
     */
    public static function booleanTerms(string $search): ?string
    {
        $cleaned = preg_replace('/[+\-><\(\)~*"@]+/u', ' ', $search) ?? '';
        $tokens  = preg_split('/\s+/u', trim($cleaned), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        if ($tokens === []) {
            return null;
        }

        foreach ($tokens as $token) {
            // One too-short token would be dropped by InnoDB, silently
            // narrowing the result set — fall back to LIKE for the whole query.
            if (mb_strlen($token) < static::MIN_TOKEN_LENGTH) {
                return null;
            }
        }

        return implode(' ', array_map(fn (string $t) => $t . '*', $tokens));
    }
}
