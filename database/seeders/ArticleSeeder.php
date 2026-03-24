<?php

namespace Database\Seeders;

use App\Models\Article;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class ArticleSeeder extends Seeder
{
    /**
     * Seed sample published articles for testing/demo.
     */
    public function run(): void
    {
        $articles = [
            [
                'title' => [
                    'en' => 'Why Passive Liveness Detection is the Future of Digital KYC',
                    'id' => 'Mengapa Passive Liveness Detection adalah Masa Depan Digital KYC',
                ],
                'slug' => 'passive-liveness-detection-future-of-digital-kyc',
                'content' => [
                    'en' => '<h2>Introduction</h2>
<p>In an era where digital identity verification is becoming mission-critical for businesses worldwide, the limitations of traditional active liveness detection are becoming more apparent. Users are tired of blinking on command or turning their heads left and right just to prove they\'re human.</p>
<h2>What is Passive Liveness Detection?</h2>
<p>Passive liveness detection works silently in the background. Instead of instructing users to perform specific actions, it analyzes a single captured image or a short video snippet using advanced deep learning models to determine whether the face presented belongs to a live person or a spoofing artifact such as a printed photo, video replay, or a 3D mask.</p>
<h3>Key Technical Advantages</h3>
<ul>
<li><strong>No user instruction required</strong> — the system works transparently</li>
<li><strong>Sub-second inference</strong> — most models process in under 300ms</li>
<li><strong>High anti-spoofing accuracy</strong> — typically above 99.5% on standard benchmark datasets</li>
<li><strong>Works across devices</strong> — from low-end smartphones to enterprise cameras</li>
</ul>
<h2>The Security Perspective</h2>
<p>Modern spoofing attacks have become frighteningly sophisticated. High-resolution 3D masks, deepfake video replays, and texture-mapped silicone masks can fool even trained security personnel. Passive liveness models, trained on millions of adversarial samples, are tuned to detect micro-texture patterns invisible to the human eye.</p>
<blockquote>
<p>"The future of identity verification is invisible to the user but impenetrable to attackers." — RBeverything Engineering Team</p>
</blockquote>
<h2>Implementation in a Laravel REST API</h2>
<p>Integrating a passive liveness model into a Laravel application can be done through a microservice architecture. The ML model runs as a Python FastAPI service, and Laravel communicates with it via an internal HTTP call:</p>
<pre><code class="language-php">$response = Http::post(config(\'services.liveness.endpoint\'), [
    \'image\'     => base64_encode($imageData),
    \'threshold\' => 0.85,
]);

if ($response->json(\'is_live\') === false) {
    return response()->json([\'error\' => \'Liveness check failed\'], 422);
}</code></pre>
<h2>Conclusion</h2>
<p>As digital fraud continues to evolve, passive liveness detection represents the gold standard in identity assurance. If you\'re building a KYC pipeline or digital onboarding flow, it\'s time to move beyond active challenges and embrace the invisible, frictionless future.</p>',
                    'id' => '<h2>Pendahuluan</h2>
<p>Di era di mana verifikasi identitas digital semakin menjadi kebutuhan kritis bagi bisnis di seluruh dunia, keterbatasan deteksi liveness aktif tradisional semakin terasa. Pengguna sudah lelah berkedip sesuai perintah atau memutar kepala mereka hanya untuk membuktikan bahwa mereka adalah manusia.</p>
<h2>Apa itu Passive Liveness Detection?</h2>
<p>Passive liveness detection bekerja secara diam-diam di latar belakang. Alih-alih menginstruksikan pengguna untuk melakukan tindakan tertentu, ia menganalisis satu gambar yang diambil atau cuplikan video singkat menggunakan model deep learning canggih untuk menentukan apakah wajah yang terlihat milik orang hidup atau artefak spoofing.</p>',
                ],
                'status'       => 'Published',
                'published_at' => Carbon::now()->subDays(5),
            ],
            [
                'title' => [
                    'en' => 'Building a Full-Stack SaaS with Laravel 12 + Filament v3',
                    'id' => 'Membangun Full-Stack SaaS dengan Laravel 12 + Filament v3',
                ],
                'slug' => 'full-stack-saas-laravel-12-filament-v3',
                'content' => [
                    'en' => '<h2>Overview</h2>
<p>Laravel 12 and Filament v3 together form one of the most powerful combinations for building modern SaaS applications. In this tutorial, we\'ll walk through architecting a multi-tenant application from scratch.</p>
<h2>Setting Up the Project</h2>
<p>Start by creating a new Laravel 12 project and installing Filament:</p>
<pre><code class="language-bash">composer create-project laravel/laravel my-saas
cd my-saas
composer require filament/filament:"^3.0"</code></pre>
<h2>Multi-Tenancy Architecture</h2>
<p>The key to a solid multi-tenant SaaS is proper data isolation. We use a <strong>shared database, separate schemas</strong> approach with Spatie\'s permission package for role-based access control.</p>
<h3>Key Components</h3>
<ul>
<li>Filament Admin Panel for super admins</li>
<li>Filament Client Panel for tenant users</li>
<li>Spatie Laravel Permission for RBAC</li>
<li>Row-level tenant scoping via model observers</li>
</ul>
<h2>Conclusion</h2>
<p>With Laravel 12 and Filament v3, building enterprise-grade SaaS applications has never been more accessible. The combination of elegant syntax, powerful ORM, and a beautiful admin UI makes it the go-to stack for modern PHP development.</p>',
                    'id' => '<h2>Gambaran Umum</h2>
<p>Laravel 12 dan Filament v3 bersama-sama membentuk salah satu kombinasi paling kuat untuk membangun aplikasi SaaS modern. Dalam tutorial ini, kita akan membahas arsitektur aplikasi multi-tenant dari awal.</p>',
                ],
                'status'       => 'Published',
                'published_at' => Carbon::now()->subDays(12),
            ],
            [
                'title' => [
                    'en' => 'Zero-Downtime Deployments on cPanel Shared Hosting',
                    'id' => 'Deployment Zero-Downtime di cPanel Shared Hosting',
                ],
                'slug' => 'zero-downtime-deployments-cpanel-shared-hosting',
                'content' => [
                    'en' => '<h2>The Challenge</h2>
<p>Most tutorials about zero-downtime deployments assume you have root server access, Docker, or at least a VPS. But what happens when your client is on shared cPanel hosting and you still need to ship code without taking the site down?</p>
<h2>Our Solution: Atomic Releases with GitHub Actions</h2>
<p>We implemented an atomic release strategy using symbolic links — the same technique used by Capistrano and Deployer — but orchestrated via GitHub Actions with SSH access to cPanel.</p>
<h3>The Release Directory Structure</h3>
<pre><code>/home/user/
├── releases/
│   ├── 20260101120000/
│   ├── 20260115093000/
│   └── 20260310150000/  ← latest
├── shared/
│   ├── .env
│   └── storage/
└── current → releases/20260310150000  ← symlink</code></pre>
<h2>GitHub Actions Workflow</h2>
<p>The key steps in our deployment pipeline are:</p>
<ol>
<li>Build assets (npm run build)</li>
<li>SSH into server and create new release directory</li>
<li>rsync files to new release</li>
<li>Link shared files (.env, storage)</li>
<li>Run migrations</li>
<li>Atomically swap the symlink</li>
<li>Clean up old releases</li>
</ol>
<h2>Results</h2>
<p>After implementing this system, our deployment downtime went from ~45 seconds (during composer install and migration) to <strong>effectively zero</strong>. The symlink swap happens in milliseconds.</p>',
                    'id' => '<h2>Tantangan</h2>
<p>Sebagian besar tutorial tentang deployment zero-downtime mengasumsikan Anda memiliki akses root server. Tapi bagaimana jika klien Anda menggunakan shared cPanel hosting dan Anda tetap perlu mengirim kode tanpa mematikan situs?</p>',
                ],
                'status'       => 'Published',
                'published_at' => Carbon::now()->subDays(20),
            ],
        ];

        foreach ($articles as $data) {
            Article::firstOrCreate(
                ['slug' => $data['slug']],
                $data
            );
        }

        $this->command->info('✅ ' . count($articles) . ' sample articles seeded successfully.');
    }
}
