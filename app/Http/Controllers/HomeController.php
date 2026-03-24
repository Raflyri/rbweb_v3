<?php

namespace App\Http\Controllers;

use App\Models\Article;
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

        // ── 4. Fallback to static sample articles if DB has none ─────────
        if (empty($articles)) {
            $articles = [
                [
                    'category'       => 'Tech Insight',
                    'category_color' => '#DC2626',
                    'title'          => 'Why Passive Liveness Detection is the Future of Digital KYC',
                    'excerpt'        => 'Exploring how passive anti-spoofing models eliminate friction while dramatically improving security.',
                    'date'           => 'Mar 2026',
                    'href'           => route('blog.index'),
                    'thumbnail'      => null,
                ],
                [
                    'category'       => 'Tutorial',
                    'category_color' => '#DC2626',
                    'title'          => 'Building a Full-Stack SaaS with Laravel 11 + Filament v3',
                    'excerpt'        => 'A deep-dive into architecting multi-tenant applications using the most powerful PHP framework combo.',
                    'date'           => 'Feb 2026',
                    'href'           => route('blog.index'),
                    'thumbnail'      => null,
                ],
                [
                    'category'       => 'DevOps',
                    'category_color' => '#DC2626',
                    'title'          => 'Zero-Downtime Deployments on cPanel Shared Hosting',
                    'excerpt'        => 'How we implemented atomic releases with GitHub Actions on a constrained hosting environment.',
                    'date'           => 'Jan 2026',
                    'href'           => route('blog.index'),
                    'thumbnail'      => null,
                ],
                [
                    'category'       => 'AI',
                    'category_color' => '#DC2626',
                    'title'          => 'Integrating TensorFlow Lite into a Laravel REST API',
                    'excerpt'        => 'Step-by-step walkthrough of exposing a computer vision model through a typed, versioned Laravel API.',
                    'date'           => 'Dec 2025',
                    'href'           => route('blog.index'),
                    'thumbnail'      => null,
                ],
            ];
        }

        // ── 5. Page data (CMS-ready) ──────────────────────────────────────
        $pageData = [

            'hero' => [
                'badge'    => 'System Online · V3 Active',
                'headline' => 'hero.headline',
                'phrases'  => 'hero.phrases',
                'subtitle' => 'hero.subtitle',
                'tagline'  => $tagline,
            ],

            'products' => [
                [
                    'id'       => 'liveness',
                    'tag_key'  => 'products.liveness.tag',
                    'name_key' => 'products.liveness.name',
                    'desc_key' => 'products.liveness.desc',
                    'cta_key'  => 'products.liveness.cta',
                    'cta_href' => '#contact',
                    'version'  => 'v1.1',
                    'accent'   => 'violet',
                ],
                [
                    'id'       => 'base64',
                    'tag_key'  => 'products.base64.tag',
                    'name_key' => 'products.base64.name',
                    'desc_key' => 'products.base64.desc',
                    'cta_key'  => 'products.base64.cta',
                    'cta_href' => 'https://tools.rbeverything.com/base64',
                    'version'  => 'v2.4',
                    'accent'   => 'sky',
                ],
                [
                    'id'       => 'portfolio',
                    'tag_key'  => 'products.portfolio.tag',
                    'name_key' => 'products.portfolio.name',
                    'desc_key' => 'products.portfolio.desc',
                    'cta_key'  => 'products.portfolio.cta',
                    'cta_href' => '/client-area/register',
                    'version'  => 'v3.0',
                    'accent'   => 'emerald',
                ],
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
                'socials' => [
                    ['name' => 'GitHub',    'href' => 'https://github.com/rbeverything',                                     'icon' => 'github'],
                    ['name' => 'LinkedIn',  'href' => ($settings->linkedin_link  ?: 'https://linkedin.com/company/rbeverything'), 'icon' => 'linkedin'],
                    ['name' => 'Instagram', 'href' => ($settings->instagram_link ?: 'https://instagram.com/rbeverything'),        'icon' => 'instagram'],
                ],
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

        return view('welcome', compact('pageData', 'i18n', 'settings'));
    }
}
