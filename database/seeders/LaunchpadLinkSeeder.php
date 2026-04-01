<?php

namespace Database\Seeders;

use App\Models\LaunchpadLink;
use Illuminate\Database\Seeder;

class LaunchpadLinkSeeder extends Seeder
{
    /**
     * Seed the initial set of Launchpad tool cards shown in the Client Area.
     *
     * Each record can be updated any time by super_admin or admin via:
     *   /rbdashboard → "Launchpad Links" (Client Area nav group)
     *
     * Rules:
     * - `required_permission` null  → visible to ALL authenticated users
     * - `required_permission` set   → only users whose role has that Spatie permission
     * - `is_active` false           → hidden from the client area without deleting
     * - `sort_order`                → drag-reorderable in the admin table
     */
    public function run(): void
    {
        $tools = [
            [
                'title'               => 'Base64 Encoder / Decoder',
                'description'         => 'Encode or decode text and files to/from Base64 format instantly.',
                'icon'                => 'code-bracket',
                'url'                 => 'https://tools.rbeverything.com/base64',
                'is_external'         => true,
                'required_permission' => null,   // public — all authenticated users
                'sort_order'          => 1,
                'is_active'           => true,
            ],
        ];

        foreach ($tools as $tool) {
            // Use firstOrCreate so re-running the seeder is idempotent (no duplicate rows)
            LaunchpadLink::firstOrCreate(
                ['url' => $tool['url']],   // unique lookup key
                $tool
            );
        }
    }
}
