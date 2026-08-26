<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Single source of truth for the locale keys used inside Article's
 * translatable JSON columns.
 *
 * The site supports four languages and stores them under ISO 639-1 codes.
 * Historically three wrong keys leaked into both code and data:
 *
 *   'my'    → actually Burmese; was meant to be Malay  ('ms')
 *   'jp'    → not a language code at all; Japanese is  ('ja')
 *   'en_US' → APP_LOCALE default, so app()->getLocale() returned a key that
 *             no article JSON ever contained, which silently broke /blog
 *             search (it queried title->en_US).
 *
 * Everything that touches a locale key goes through normalize() so a stray
 * value can never reach a query or a JSON key again.
 */
class ArticleLocale
{
    /** The only locale keys that may be written to the database. */
    public const SUPPORTED = ['en', 'id', 'ms', 'ja'];

    /** Used when a locale cannot be mapped to anything supported. */
    public const FALLBACK = 'en';

    /**
     * Wrong or region-qualified codes mapped to their canonical key.
     * Lookups are done lower-cased with '-' folded to '_'.
     */
    public const ALIASES = [
        'my'    => 'ms',   // legacy typo for Malay
        'jp'    => 'ja',   // legacy typo for Japanese
        'en_us' => 'en',
        'en_gb' => 'en',
        'en_au' => 'en',
        'id_id' => 'id',
        'in'    => 'id',   // deprecated ISO code for Indonesian
        'ms_my' => 'ms',
        'ja_jp' => 'ja',
    ];

    /**
     * Editor tab order and display labels. Indonesian leads because it is the
     * required primary locale in the Client Area editor.
     */
    public const LABELS = [
        'id' => 'Indonesia',
        'ms' => 'Melayu',
        'en' => 'English',
        'ja' => '日本語',
    ];

    /**
     * Exact spellings of wrong keys that already exist in stored JSON.
     * MySQL JSON paths are case sensitive, so these must match byte for byte
     * (unlike ALIASES, which is looked up case-insensitively).
     */
    public const LEGACY_KEYS = ['my', 'jp', 'en_US', 'en_GB'];

    /** Map any locale string onto a supported key. */
    public static function normalize(?string $locale): string
    {
        $key = Str::lower(str_replace('-', '_', trim((string) $locale)));

        if ($key === '') {
            return static::FALLBACK;
        }

        if (in_array($key, static::SUPPORTED, true)) {
            return $key;
        }

        if (isset(static::ALIASES[$key])) {
            return static::ALIASES[$key];
        }

        // "fr_CA" and friends: try the bare language subtag before giving up.
        $base = Str::before($key, '_');

        if ($base !== $key) {
            if (in_array($base, static::SUPPORTED, true)) {
                return $base;
            }

            if (isset(static::ALIASES[$base])) {
                return static::ALIASES[$base];
            }
        }

        return static::FALLBACK;
    }

    /** The active application locale, normalised to a storable key. */
    public static function current(): string
    {
        return static::normalize(app()->getLocale());
    }

    /**
     * Every key worth checking when resolving a slug, newest first.
     *
     * Legacy keys are included on purpose: until articles:fix-locale-keys has
     * run everywhere, an old row may still carry a slug under 'my'/'jp'/'en_US'
     * and those permalinks must keep resolving.
     *
     * @return array<int, string>
     */
    public static function lookupKeys(): array
    {
        return array_values(array_unique(array_merge(
            static::SUPPORTED,
            static::LEGACY_KEYS,
        )));
    }

    /**
     * Supported locales in editor display order.
     *
     * @return array<int, string>
     */
    public static function editorLocales(): array
    {
        return array_keys(static::LABELS);
    }

    /** Is this already a canonical key? */
    public static function isSupported(?string $locale): bool
    {
        return in_array((string) $locale, static::SUPPORTED, true);
    }
}
