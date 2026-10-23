<?php

namespace Database\Seeders;

use App\Models\SoftwareCatalog;
use App\Models\SoftwareCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SoftwareCatalogSeeder extends Seeder
{
    /**
     * Seed initial software catalog items and categories.
     */
    public function run(): void
    {
        // 1. Seed Dynamic Categories
        $categoriesData = [
            [
                'name'        => 'EdTech / Sekolah',
                'description' => 'Solusi perangkat lunak untuk institusi pendidikan, sekolah, dan ujian digital.',
                'sort_order'  => 1,
            ],
            [
                'name'        => 'Enterprise / Retail',
                'description' => 'Sistem manajemen operasional bisnis, Point of Sale, kasir, dan inventori.',
                'sort_order'  => 2,
            ],
            [
                'name'        => 'FinTech & Layanan',
                'description' => 'Platform billing, payment gateway, dan manajemen transaksi digital.',
                'sort_order'  => 3,
            ],
        ];

        $categories = [];
        foreach ($categoriesData as $cat) {
            $categories[$cat['name']] = SoftwareCategory::updateOrCreate(
                ['slug' => Str::slug($cat['name'])],
                $cat
            );
        }

        // 2. Seed Software with Categories & Release Info
        $items = [
            [
                'title'                => 'Digitalize-Exam (CBT)',
                'slug'                 => 'digitalize-exam-cbt',
                'software_category_id' => $categories['EdTech / Sekolah']->id,
                'category'             => 'EdTech / Sekolah',
                'version'              => '2.0',
                'release_date'         => '2026-10-15',
                'badge_text'           => 'v2.0 Tested',
                'short_description'    => 'Platform ujian digital interaktif generasi terbaru dengan dukungan KaTeX, sinkronisasi offline, monitoring anti-kecurangan real-time, dan ekspor nilai otomatis.',
                'description'          => "Digitalize-Exam dirancang untuk mendigitalisasi proses evaluasi akademik sekolah dan universitas secara menyeluruh. \n\nDilengkapi dengan sistem anti-curang canggih yang mendeteksi perpindahan tab browser, dukungan penuh penulisan rumus eksakta berbasis KaTeX, serta arsitektur PWA yang mampu mengamankan jawaban siswa meskipun koneksi internet terputus di tengah ujian.",
                'tech_stack'           => ['Laravel', 'Inertia.js', 'MySQL', 'Tailwind CSS', 'KaTeX', 'PWA'],
                'features'             => [
                    'Bank Soal Dinamis & Pengacakan Soal/Opsi Otomatis',
                    'Deteksi Pergantian Tab & Anti-Kecurangan Real-Time',
                    'Dukungan Notasi Matematika & Rumus KaTeX',
                    'Penyimpanan Jawaban Lokal & Sinkronisasi Offline PWA',
                    'Ekspor Rekap Hasil Ujian & Analisis Butir Soal ke Excel',
                ],
                'changelog'            => "• Pembaruan v2.0: Dukungan rendering formula KaTeX real-time.\n• Integrasi pendeteksi tab browser siswa untuk cegah kecurangan.\n• Mode offline-first PWA yang menyimpan lembar jawaban ke indexedDB.",
                'demo_url'             => 'https://digitalization-exam.rbeverything.com/',
                'demo_type'            => 'direct',
                'demo_credentials'     => 'siswa@sekolah.com / password',
                'status'               => SoftwareCatalog::STATUS_TAYANGKAN,
                'is_featured'          => true,
                'sort_order'           => 1,
            ],
            [
                'title'                => 'Sistem POS & ERP (Toko Bangunan)',
                'slug'                 => 'pos-erp-toko-bangunan',
                'software_category_id' => $categories['Enterprise / Retail']->id,
                'category'             => 'Enterprise / Retail',
                'version'              => '1.0',
                'release_date'         => '2026-10-01',
                'badge_text'           => 'Ready to Deploy',
                'short_description'    => 'Sistem manajemen kasir, inventori multi-satuan (sak, kg, meter, lonjor), piutang pelanggan/kasbon, dan kalkulasi margin laba real-time khusus bisnis toko bahan bangunan & ritel.',
                'description'          => "Sistem POS & ERP Toko Bangunan mengatasi tantangan terbesar toko material ritel: pengelolaan multi-satuan barang dan sistem pembayaran tempo/kasbon.\n\nDengan sistem konversi satuan otomatis, kasir dapat menjual barang dalam satuan eceran (misal: 1/2 kg paku atau 3 meter pipa) tanpa mengacaukan stok master satuan besar. Dilengkapi modul pencatatan piutang pelanggan dengan riwayat pembayaran bertahap dan batas kredit otomatis.",
                'tech_stack'           => ['Next.js', 'TypeScript', 'Prisma ORM', 'Tailwind CSS', 'PostgreSQL / SQLite', 'Docker'],
                'features'             => [
                    'Konversi Multi-Satuan Fleksibel (Sak, Kg, Meter, Lembar, Lonjor)',
                    'Kasir Nota Cepat & Pencatatan Piutang / Kasbon Pelanggan',
                    'Manajemen Stok Gudang & Peringatan Batas Minimum Stok',
                    'Kalkulasi Margin Laba Bersih Per Transaksi Secara Real-Time',
                    'Laporan Arus Kas Harian & Laba Rugi Toko Otomatis',
                ],
                'changelog'            => "• Pembaruan v1.0: Rilis arsitektur Next.js & Prisma ORM terisolasi container.\n• Modul multi-satuan dengan konversi desimal presisi.\n• Pencatatan hutang piutang pelanggan dengan riwayat cicilan.",
                'demo_url'             => 'https://pos-demo.rbeverything.com/',
                'demo_type'            => 'direct',
                'demo_credentials'     => 'Role Kasir: kasir_demo / Role Admin: admin_demo',
                'status'               => SoftwareCatalog::STATUS_TAYANGKAN,
                'is_featured'          => true,
                'sort_order'           => 2,
            ],
        ];

        foreach ($items as $data) {
            SoftwareCatalog::updateOrCreate(
                ['slug' => $data['slug']],
                $data
            );
        }
    }
}
