<?php

namespace Database\Seeders;

use App\Models\LaunchpadLink;
use Illuminate\Database\Seeder;

class LaunchpadLinkSeeder extends Seeder
{
    /**
     * Seed the initial Launchpad tool cards.
     *
     * `show_on_homepage = true`  → also shown as a product card on the public homepage
     * `show_on_homepage = false` → only in the client-area dashboard
     *
     * All fields can be updated any time via /rbdashboard → "Launchpad Links".
     * Only super_admin and admin can access that resource.
     */
    public function run(): void
    {
        $tools = [
            // ── Card 1: Passive Liveness Detection ─────────────────────────────
            [
                'title'              => 'Passive Liveness Detection',
                'description'       => 'Frictionless identity verification. Anti-spoofing biometric detection that distinguishes real faces from photos, videos, and 3D masks — without user interaction.',
                'icon'              => 'eye',
                'url'               => '#contact',
                'is_external'       => false,
                'required_permission'=> null,
                'sort_order'        => 1,
                'is_active'         => true,
                // ── Homepage card fields ──────────────────────────────────────
                'show_on_homepage'  => true,
                'homepage_badge'    => 'AI · Computer Vision',
                'homepage_accent'   => 'violet',
                'version'           => 'v1.1',
                'homepage_cta_label'=> 'Learn More',
                'card_template'     => 'liveness',
            ],

            // ── Card 2: Base64 Suite ────────────────────────────────────────────
            [
                'title'              => 'Base64 Suite',
                'description'       => 'Encode, decode, and validate Base64 strings in real-time with support for URL-safe variants.',
                'icon'              => 'code-bracket',
                'url'               => 'https://tools.rbeverything.com/base64',
                'is_external'       => true,
                'required_permission'=> null,
                'sort_order'        => 2,
                'is_active'         => true,
                // ── Homepage card fields ──────────────────────────────────────
                'show_on_homepage'  => true,
                'homepage_badge'    => 'Encoder / Decoder',
                'homepage_accent'   => 'sky',
                'version'           => 'v2.4',
                'homepage_cta_label'=> 'Open Tool',
                'card_template'     => 'base64',
            ],

            // ── Card 3: Portfolio Platform ──────────────────────────────────────
            [
                'title'              => 'Portfolio Platform',
                'description'       => 'Every user gets a personalised /@slug page to showcase skills, experience, and achievements.',
                'icon'              => 'user-circle',
                'url'               => '/client-area/register',
                'is_external'       => false,
                'required_permission'=> null,
                'sort_order'        => 3,
                'is_active'         => true,
                // ── Homepage card fields ──────────────────────────────────────
                'show_on_homepage'  => true,
                'homepage_badge'    => 'Platform',
                'homepage_accent'   => 'emerald',
                'version'           => 'v3.0',
                'homepage_cta_label'=> 'Build Your Page',
                'card_template'     => 'portfolio',
            ],
        ];

        foreach ($tools as $tool) {
            // updateOrCreate on URL — re-running the seeder always syncs all fields
            LaunchpadLink::updateOrCreate(
                ['url' => $tool['url']],
                $tool
            );
        }
    }
}
