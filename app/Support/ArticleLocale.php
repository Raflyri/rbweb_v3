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

    /**
     * The locales currently offered to visitors and editors — Indonesian first,
     * because it is the site default.
     *
     * Malay and Japanese are switched off for now, not removed: they stay in
     * SUPPORTED, their lang/*.json files stay on disk, and any translation
     * already stored under 'ms' or 'ja' is left untouched in the database. Put
     * them back in this list and they light up again exactly as they were.
     *
     * Everything that offers a language to a human reads this list: the public
     * switcher, the editor tabs, the homepage i18n bundle, and SetLocale, which
     * refuses a session still holding a locale that is no longer on it.
     *
     * @var array<int, string>
     */
    public const ENABLED = ['id', 'en'];

    /**
     * Used when a locale cannot be mapped to an enabled one.
     *
     * Indonesian, matching APP_LOCALE: this is an Indonesian business whose
     * visitors mostly read Indonesian, and English is the deliberate choice
     * rather than the assumed default.
     */
    public const FALLBACK = 'id';

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
     * Locales an editor may write in, in display order.
     *
     * Only the enabled ones: writing copy in a language no visitor can select
     * is work nobody can read.
     *
     * @return array<int, string>
     */
    public static function editorLocales(): array
    {
        return static::ENABLED;
    }

    /**
     * [locale => label] for the enabled locales only, in ENABLED order.
     *
     * @return array<string, string>
     */
    public static function enabledLabels(): array
    {
        $labels = [];

        foreach (static::ENABLED as $locale) {
            $labels[$locale] = static::LABELS[$locale] ?? strtoupper($locale);
        }

        return $labels;
    }

    /**
     * The two-letter badge shown on a switcher button.
     *
     * Malay is 'ms' in code but reads as MY to the people who speak it, which
     * is why the button and the storage key disagree.
     */
    public static function badge(string $locale): string
    {
        return $locale === 'ms' ? 'MY' : strtoupper($locale);
    }

    /** Is this already a canonical key? */
    public static function isSupported(?string $locale): bool
    {
        return in_array((string) $locale, static::SUPPORTED, true);
    }

    /** Is this locale currently offered to visitors and editors? */
    public static function isEnabled(?string $locale): bool
    {
        return in_array((string) $locale, static::ENABLED, true);
    }
}
