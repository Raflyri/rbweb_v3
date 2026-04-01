<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\LaunchpadLink;
use App\Settings\GeneralSettings;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class HomeController extends Controller
{
    /**
     * Render the homepage with CMS-driven page data and i18n strings.
     */
    public function index(GeneralSettings $settings)
    {
        $locale = app()->getLocale();

        // ── 1. Load all locale translations for client-side i18n switcher ──
        $supportedLocales = ['en', 'id', 'ms', 'ja'];
        $i18n = [];
        foreach ($supportedLocales as $lang) {
            $path = lang_path("{$lang}.json");
            if (File::exists($path)) {
                $i18n[$lang] = json_decode(File::get($path), true);
            }
        }

        // ── 2. Resolve tagline with fallback to English ───────────────────
        $tagline = $settings->web_tagline[$locale]
            ?? $settings->web_tagline['en']
            ?? 'Your Partner in the Digital Age';

        // ── 3. Fetch published articles from DB (locale-aware, EN fallback) ─
        $articles = Article::published()
            ->latest('published_at')
            ->take(4)
            ->get()
            ->map(function (Article $article) use ($locale) {
                $title   = $article->getTranslation('title', $locale, true);
                $content = $article->getTranslation('content', $locale, true);

                return [
                    'category'       => 'Article',
                    'category_color' => '#DC2626',
                    'title'          => $title,
                    'excerpt'        => Str::limit(strip_tags($content), 130),
                    'date'           => $article->published_at?->format('M d, Y') ?? '',
                    'href'           => route('blog.show', $article->slug),
                    'thumbnail'      => $article->thumbnail
                        ? asset('storage/' . $article->thumbnail)
                        : null,
                ];
            })
            ->toArray();


        // ── 4. Fetch active homepage product cards from DB ────────────────────
        $products = LaunchpadLink::where('is_active', true)
            ->where('show_on_homepage', true)
            ->orderBy('sort_order')
            ->get();

        // ── 5. Page data (CMS-ready) ──────────────────────────────────────
        $pageData = [

            'hero' => [
                'badge'    => 'System Online · V3 Active',
                'headline' => 'hero.headline',
                'phrases'  => 'hero.phrases',
                'subtitle' => 'hero.subtitle',
                'tagline'  => $tagline,
            ],

            'services' => [
                ['key' => 'consulting', 'icon' => 'monitor',   'color' => '#38BDF8'],
                ['key' => 'webdev',     'icon' => 'code',       'color' => '#818CF8'],
                ['key' => 'ai',         'icon' => 'layers',     'color' => '#A78BFA'],
                ['key' => 'devops',     'icon' => 'settings',   'color' => '#34D399'],
                ['key' => 'training',   'icon' => 'terminal',   'color' => '#FB7185'],
                ['key' => 'product',    'icon' => 'zap',        'color' => '#FBBF24'],
            ],

            'tech_stack' => [
                'Laravel', 'Python', 'React', 'Docker',
                'TensorFlow', 'MySQL', 'Redis', 'Linux',
                'Vue.js', 'Filament', 'Nginx', 'GitHub Actions',
            ],

            'workflow_steps' => ['discovery', 'design', 'development', 'liftoff'],

            'articles' => $articles,

            'footer' => [
                // Dynamic values from GeneralSettings
                'email'         => $settings->contact_email ?: 'hello@rbeverything.com',
                'whatsapp_url'  => $settings->whatsapp_number
                    ? 'https://wa.me/' . preg_replace('/\D/', '', $settings->whatsapp_number)
                    : null,

                // Social links: only include entries where the URL is set.
                // Null entries are silently omitted — the blade loops this array.
                'socials' => array_filter([
                    $settings->linkedin_link  ? ['name' => 'LinkedIn',  'href' => $settings->linkedin_link,  'icon' => 'linkedin']  : null,
                    $settings->instagram_link ? ['name' => 'Instagram', 'href' => $settings->instagram_link, 'icon' => 'instagram'] : null,
                    $settings->youtube_link   ? ['name' => 'YouTube',   'href' => $settings->youtube_link,   'icon' => 'youtube']   : null,
                    $settings->twitter_link   ? ['name' => 'Twitter',   'href' => $settings->twitter_link,   'icon' => 'twitter']   : null,
                    $settings->github_link    ? ['name' => 'GitHub',    'href' => $settings->github_link,    'icon' => 'github']    : null,
                ]),
                'quick_links' => [
                    ['label_key' => 'nav.products', 'href' => '#products'],
                    ['label_key' => 'nav.services', 'href' => '#services'],
                    ['label_key' => 'nav.about',    'href' => '#about'],
                    ['label_key' => 'nav.blog',     'href' => '/blog'],
                    ['label'     => 'Admin Panel',  'href' => '/rbdashboard'],
                ],
                'resources' => [
                    ['label_key' => 'blog.title',    'href' => '/blog'],
                    ['label'     => 'Documentation', 'href' => '#'],
                    ['label'     => 'FAQ',           'href' => '#'],
                    ['label'     => 'Changelog',     'href' => '#'],
                    ['label'     => 'Client Area',   'href' => '/client-area'],
                ],
            ],

        ];

        // Pass brand assets to the view for logo rendering
        $siteLogo    = $settings->site_logo    ? asset('storage/' . $settings->site_logo)    : null;
        $siteFavicon = $settings->site_favicon ? asset('storage/' . $settings->site_favicon) : null;
        $siteName    = $settings->site_name    ?? config('app.name', 'RBeverything');

        return view('welcome', compact('pageData', 'i18n', 'settings', 'siteLogo', 'siteFavicon', 'siteName', 'products'));
    }
}
