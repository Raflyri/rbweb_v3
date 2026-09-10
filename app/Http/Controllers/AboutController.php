<?php

namespace App\Http\Controllers;

use App\Settings\AboutSettings;
use App\Settings\GeneralSettings;
use App\Support\ArticleLocale;
use Illuminate\View\View;

class AboutController extends Controller
{
    /**
     * Render the dedicated About Us (Tentang Kami) page.
     */
    public function index(AboutSettings $aboutSettings, GeneralSettings $generalSettings): View
    {
        $locale = ArticleLocale::current();

        // ── Helper closure to resolve translatable fields ─────────────────────
        $resolve = function (?array $field, string $fallback = '') use ($locale): string {
            if (empty($field)) {
                return $fallback;
            }
            return $field[$locale] ?? $field['id'] ?? $field['en'] ?? $fallback;
        };

        $content = [
            'hero_badge'      => $resolve($aboutSettings->hero_badge, 'Tentang RBeverything'),
            'hero_title'      => $resolve($aboutSettings->hero_title, 'Kami percaya teknologi seharusnya terasa mudah'),
            'hero_subtitle'   => $resolve($aboutSettings->hero_subtitle),
            'story_title'     => $resolve($aboutSettings->story_title),
            'story_content'   => $resolve($aboutSettings->story_content),
            'vision'          => $resolve($aboutSettings->vision),
            'mission'         => $resolve($aboutSettings->mission),
            'stats'           => $aboutSettings->stats ?? [],
            'core_values'     => $aboutSettings->core_values ?? [],
            'cta_title'       => $resolve($aboutSettings->cta_title),
            'cta_subtitle'    => $resolve($aboutSettings->cta_subtitle),
            'cta_button_text' => $resolve($aboutSettings->cta_button_text, 'Ayo Berkolaborasi'),
            'cta_button_url'  => $aboutSettings->cta_button_url ?: 'mailto:hello@rbeverything.com',
        ];

        // Format missions if stored as multi-line string
        $missionList = array_filter(array_map('trim', explode("\n", $content['mission'])));

        // Site assets
        $siteLogo    = $generalSettings->site_logo ? asset('storage/' . $generalSettings->site_logo) : null;
        $siteFavicon = $generalSettings->site_favicon ? asset('storage/' . $generalSettings->site_favicon) : null;
        $siteName    = $generalSettings->site_name ?? config('app.name', 'RBeverything');

        return view('about.index', compact(
            'content',
            'missionList',
            'locale',
            'aboutSettings',
            'generalSettings',
            'siteLogo',
            'siteFavicon',
            'siteName'
        ));
    }
}
