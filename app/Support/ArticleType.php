<?php

namespace App\Support;

/**
 * The content-type taxonomy used to distinguish Blog / Article / News posts
 * that otherwise share the exact same Article model, workflow, and forms.
 *
 * This is deliberately a tag on an existing row, not a separate model or
 * route structure — /blog stays the one public listing, filterable by type.
 */
class ArticleType
{
    public const BLOG = 'Blog';
    public const ARTICLE = 'Article';
    public const NEWS = 'News';

    public const DEFAULT = self::ARTICLE;

    /** @var array<int, string> */
    public const OPTIONS = [self::BLOG, self::ARTICLE, self::NEWS];

    /** Filament Select-ready [value => label] map. */
    public static function options(): array
    {
        return array_combine(self::OPTIONS, self::OPTIONS);
    }

    public static function isValid(?string $type): bool
    {
        return in_array($type, self::OPTIONS, true);
    }

    /** Bootstrap/Filament badge colour per type — kept in one place. */
    public static function color(?string $type): string
    {
        return match ($type) {
            self::NEWS  => 'danger',
            self::BLOG  => 'info',
            default     => 'gray', // Article
        };
    }
}
