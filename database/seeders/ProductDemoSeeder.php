<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Support\ProductType;
use Illuminate\Database\Seeder;

/**
 * SAMPLE DATA FOR DEVELOPMENT ONLY — never wire this into DatabaseSeeder.
 *
 * .github/workflows/deploy.yml runs `db:seed` (i.e. DatabaseSeeder) against
 * production and sandbox after every single deploy. Anything reachable from
 * DatabaseSeeder therefore lands on the live site automatically — which is how
 * junk articles reached production once already. Keeping this seeder
 * standalone is the whole safeguard, so run it by hand and only locally:
 *
 *     php artisan db:seed --class=ProductDemoSeeder
 *
 * The environment guard below is the second lock: even a hand-typed command on
 * the production server refuses to plant demo rows.
 */
class ProductDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            $this->command?->error('ProductDemoSeeder is sample data and refuses to run in production.');

            return;
        }

        $samples = [
            [
                // Carries copy in all four locales on purpose. Only ID and EN
                // are switched on right now, so this row also demonstrates that
                // disabling a language hides it without touching what is stored:
                // put 'ms' back in ArticleLocale::ENABLED and the Malay text is
                // simply there again.
                'type'  => ProductType::BARANG,
                'price' => 450000,
                'stock' => 12,
                'name'  => [
                    'id' => 'Paket Router Wi-Fi 6 Terpasang',
                    'en' => 'Wi-Fi 6 Router Bundle (Installed)',
                    'ms' => 'Pakej Penghala Wi-Fi 6 (Dipasang)',
                    'ja' => 'Wi-Fi 6 ルーター設置パッケージ',
                ],
                'short' => [
                    'id' => 'Router Wi-Fi 6 dual-band lengkap dengan konfigurasi awal dan pemasangan di lokasi.',
                    'en' => 'Dual-band Wi-Fi 6 router including initial configuration and on-site installation.',
                    'ms' => 'Penghala Wi-Fi 6 dwijalur lengkap dengan konfigurasi awal dan pemasangan di lokasi.',
                    'ja' => 'デュアルバンド Wi-Fi 6 ルーター。初期設定と現地設置を含みます。',
                ],
            ],
            [
                'type'  => ProductType::BARANG,
                'price' => 125000,
                'stock' => null,
                'name'  => ['id' => 'Kabel LAN Cat6 Terminasi (per meter)', 'en' => 'Terminated Cat6 LAN Cable (per metre)'],
                'short' => [
                    'id' => 'Kabel Cat6 dipotong sesuai kebutuhan, sudah terpasang konektor RJ45.',
                    'en' => 'Cat6 cable cut to length with RJ45 connectors already crimped.',
                ],
            ],
            [
                'type'  => ProductType::BARANG,
                'price' => null,
                'stock' => null,
                'name'  => ['id' => 'Rakit PC Workstation Custom', 'en' => 'Custom Workstation PC Build'],
                'short' => [
                    'id' => 'Spesifikasi disesuaikan kebutuhan dan anggaran — harga menyusul setelah konsultasi.',
                    'en' => 'Specification tailored to your needs and budget — quoted after a short consultation.',
                ],
            ],
            [
                'type'  => ProductType::JASA,
                'price' => 750000,
                'stock' => null,
                'name'  => ['id' => 'Audit Keamanan Website (1 Domain)', 'en' => 'Website Security Audit (1 Domain)'],
                'short' => [
                    'id' => 'Pemeriksaan celah keamanan umum plus laporan temuan dan rekomendasi perbaikan.',
                    'en' => 'A sweep for common vulnerabilities plus a findings-and-fixes report.',
                ],
            ],
            [
                'type'  => ProductType::JASA,
                'price' => 2500000,
                'stock' => null,
                'name'  => ['id' => 'Pembuatan Landing Page Profil Usaha', 'en' => 'Business Profile Landing Page'],
                'short' => [
                    'id' => 'Satu halaman profil usaha responsif, siap tayang, termasuk domain tahun pertama.',
                    'en' => 'A responsive single-page business profile, launch-ready, first-year domain included.',
                ],
            ],
            [
                'type'  => ProductType::JASA,
                'price' => null,
                'stock' => null,
                'name'  => ['id' => 'Pendampingan Migrasi Server', 'en' => 'Server Migration Assistance'],
                'short' => [
                    'id' => 'Pendampingan pindah hosting atau server, dihitung berdasarkan skala sistemnya.',
                    'en' => 'Hands-on help moving hosts or servers, priced by the size of the system.',
                ],
            ],
        ];

        foreach ($samples as $index => $sample) {
            Product::updateOrCreate(
                ['slug' => \Illuminate\Support\Str::slug($sample['name']['id'])],
                [
                    'name'              => $sample['name'],
                    'short_description' => $sample['short'],
                    // One paragraph per language the sample was written in, so a
                    // row with Malay/Japanese copy has a Malay/Japanese detail
                    // page too rather than falling back to Indonesian there.
                    'description'       => collect($sample['short'])
                        ->map(fn (string $text) => '<p>' . $text . '</p><p>[data contoh — development only]</p>')
                        ->all(),
                    'type'              => $sample['type'],
                    'price'             => $sample['price'],
                    'currency'          => 'IDR',
                    'stock'             => $sample['stock'],
                    'is_active'         => true,
                    'is_featured'       => $index < 2,
                    'sort_order'        => $index,
                ],
            );
        }

        $this->command?->info('Seeded ' . count($samples) . ' sample products (development only).');
    }
}
