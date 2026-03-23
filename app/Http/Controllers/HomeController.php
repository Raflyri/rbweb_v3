<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;

class HomeController extends Controller
{
    /**
     * Render the homepage with CMS-ready page data and i18n strings.
     */
    public function index()
    {
        // ── 1. Load all locale translations for client-side i18n switcher ──
        $supportedLocales = ['en', 'id', 'ms', 'ja'];
        $i18n = [];
        foreach ($supportedLocales as $locale) {
            $path = lang_path("{$locale}.json");
            if (File::exists($path)) {
                $i18n[$locale] = json_decode(File::get($path), true);
            }
        }

        // ── 2. CMS-ready page data (populated from DB in the future) ──
        // These defaults allow the page to render before any DB content exists.
        // When you connect the Filament CMS, replace these with model queries,
        // using policies that restrict writes to Super Admin & Admin roles.
        $pageData = [

            'hero' => [
                // Future: HomeSetting::localized()->hero
                'badge'    => 'System Online · V3 Active',
                'headline' => 'hero.headline',          // i18n key
                'phrases'  => 'hero.phrases',            // i18n key — array
                'subtitle' => 'hero.subtitle',           // i18n key
            ],

            'products' => [
                // Future: Product::active()->ordered()->get()
                [
                    'id'          => 'liveness',
                    'tag_key'     => 'products.liveness.tag',
                    'name_key'    => 'products.liveness.name',
                    'desc_key'    => 'products.liveness.desc',
                    'cta_key'     => 'products.liveness.cta',
                    'cta_href'    => '#contact',
                    'version'     => 'v1.1',
                    'accent'      => 'violet',
                ],
                [
                    'id'          => 'base64',
                    'tag_key'     => 'products.base64.tag',
                    'name_key'    => 'products.base64.name',
                    'desc_key'    => 'products.base64.desc',
                    'cta_key'     => 'products.base64.cta',
                    'cta_href'    => 'https://tools.rbeverything.com/base64',
                    'version'     => 'v2.4',
                    'accent'      => 'sky',
                ],
                [
                    'id'          => 'portfolio',
                    'tag_key'     => 'products.portfolio.tag',
                    'name_key'    => 'products.portfolio.name',
                    'desc_key'    => 'products.portfolio.desc',
                    'cta_key'     => 'products.portfolio.cta',
                    'cta_href'    => '/client-area/register',
                    'version'     => 'v3.0',
                    'accent'      => 'emerald',
                ],
            ],

            'services' => [
                // Future: Service::active()->ordered()->get()
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

            'articles' => [
                // Future: Article::published()->latest()->take(5)->get()
                [
                    'category' => 'Tech Insight',
                    'category_color' => '#38BDF8',
                    'title'    => 'Why Passive Liveness Detection is the Future of Digital KYC',
                    'excerpt'  => 'Exploring how passive anti-spoofing models eliminate friction while dramatically improving security.',
                    'date'     => 'Mar 2026',
                    'href'     => '#',
                ],
                [
                    'category' => 'Tutorial',
                    'category_color' => '#818CF8',
                    'title'    => 'Building a Full-Stack SaaS with Laravel 11 + Filament v3',
                    'excerpt'  => 'A deep-dive into architecting multi-tenant applications using the most powerful PHP framework combo.',
                    'date'     => 'Feb 2026',
                    'href'     => '#',
                ],
                [
                    'category' => 'DevOps',
                    'category_color' => '#34D399',
                    'title'    => 'Zero-Downtime Deployments on cPanel Shared Hosting',
                    'excerpt'  => 'How we implemented atomic releases with GitHub Actions on a constrained hosting environment.',
                    'date'     => 'Jan 2026',
                    'href'     => '#',
                ],
                [
                    'category' => 'AI',
                    'category_color' => '#A78BFA',
                    'title'    => 'Integrating TensorFlow Lite into a Laravel REST API',
                    'excerpt'  => 'Step-by-step walkthrough of exposing a computer vision model through a typed, versioned Laravel API.',
                    'date'     => 'Dec 2025',
                    'href'     => '#',
                ],
                [
                    'category' => 'Security',
                    'category_color' => '#FB7185',
                    'title'    => 'RBAC Done Right: Role-Based Access Control in Filament v3',
                    'excerpt'  => 'Designing a dual-dashboard system with strict policy enforcement and Spatie Permission integration.',
                    'date'     => 'Nov 2025',
                    'href'     => '#',
                ],
            ],

            'footer' => [
                'email'   => 'hello@rbeverything.com',
                'socials' => [
                    ['name' => 'GitHub',    'href' => 'https://github.com/rbeverything',           'icon' => 'github'],
                    ['name' => 'LinkedIn',  'href' => 'https://linkedin.com/company/rbeverything',  'icon' => 'linkedin'],
                    ['name' => 'Instagram', 'href' => 'https://instagram.com/rbeverything',         'icon' => 'instagram'],
                ],
                'quick_links' => [
                    ['label_key' => 'nav.products', 'href' => '#products'],
                    ['label_key' => 'nav.services', 'href' => '#services'],
                    ['label_key' => 'nav.about',    'href' => '#about'],
                    ['label_key' => 'nav.blog',     'href' => '#blog'],
                    ['label'     => 'Admin Panel',  'href' => '/rbdashboard'],
                ],
                'resources' => [
                    ['label_key' => 'blog.title',         'href' => '#blog'],
                    ['label'     => 'Documentation',      'href' => '#'],
                    ['label'     => 'FAQ',                'href' => '#'],
                    ['label'     => 'Changelog',          'href' => '#'],
                    ['label'     => 'Client Area',        'href' => '/client-area'],
                ],
            ],

        ];

        return view('welcome', compact('pageData', 'i18n'));
    }
}
