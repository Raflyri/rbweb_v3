<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        // ── Stats Grid (Shared with Homepage) ───────────────────────────
        $this->migrator->add('about.stats', [
            [
                'value' => '10+',
                'label_id' => 'Produk diluncurkan',
                'label_en' => 'Products shipped',
            ],
            [
                'value' => '3+',
                'label_id' => 'Tahun pengalaman',
                'label_en' => 'Years of experience',
            ],
            [
                'value' => '∞',
                'label_id' => 'Komitmen terhadap kualitas',
                'label_en' => 'Commitment to quality',
            ],
        ]);

        // ── Hero Section ────────────────────────────────────────────────
        $this->migrator->add('about.hero_badge', [
            'id' => 'Tentang RBeverything',
            'en' => 'About RBeverything',
        ]);
        $this->migrator->add('about.hero_title', [
            'id' => 'Kami percaya teknologi seharusnya terasa mudah',
            'en' => 'We believe technology should feel effortless',
        ]);
        $this->migrator->add('about.hero_subtitle', [
            'id' => 'RBeverything adalah studio teknologi yang bersemangat membangun produk yang tidak hanya powerful tetapi juga menyenangkan digunakan. Dari developer solo hingga perusahaan berkembang, kami membantu organisasi memanfaatkan alat yang tepat di waktu yang tepat.',
            'en' => 'RBeverything is a technology studio passionate about building products that are not only powerful but delightful to use. From solo developers to growing enterprises, we help organizations leverage the right tools at the right time.',
        ]);

        // ── Story & Philosophy ──────────────────────────────────────────
        $this->migrator->add('about.story_title', [
            'id' => 'Dedikasi Tanpa Batas untuk Solusi Digital Berkualitas',
            'en' => 'Boundless Dedication to High-Caliber Digital Solutions',
        ]);
        $this->migrator->add('about.story_content', [
            'id' => "<p>Didirikan dengan visi untuk mereduksi kompleksitas teknologi, <strong>RBeverything</strong> berfokus pada rekayasa perangkat lunak, sistem cloud modern, dan kecerdasan buatan yang dirancang untuk skala jangka panjang.</p><p>Kami memadukan ketelitian teknis dengan estetika desain modern agar setiap sistem yang kami bangun tidak hanya berkinerja tinggi, namun juga intuitif dan memberikan dampak bisnis nyata bagi setiap mitra dan klien kami.</p>",
            'en' => "<p>Founded with the vision to demystify technological complexity, <strong>RBeverything</strong> focuses on modern software engineering, cloud architectures, and applied artificial intelligence built for sustainable scale.</p><p>We fuse engineering precision with modern aesthetics so that every system we build is not just high-performing, but intuitive and genuinely impactful for our partners and clients.</p>",
        ]);

        // ── Vision & Mission ────────────────────────────────────────────
        $this->migrator->add('about.vision', [
            'id' => 'Menjadi studio teknologi terdepan yang mendefinisikan standar baru dalam kemudahan, kehandalan, dan inovasi digital.',
            'en' => 'To become a leading technology studio setting new benchmarks in digital simplicity, reliability, and innovation.',
        ]);
        $this->migrator->add('about.mission', [
            'id' => "1. Membangun produk dan infrastruktur digital berkualitas tinggi dengan arsitektur masa depan.\n2. Mempermudah organisasi dan pengembang dalam mengadopsi teknologi modern dan kecerdasan buatan.\n3. Memberikan solusi berorientasi hasil yang presisi, aman, dan berkesinambungan.",
            'en' => "1. Craft high-caliber digital products and infrastructure with future-proof architecture.\n2. Empower organizations and developers to seamlessly adopt modern technology and AI.\n3. Deliver precision-driven, secure, and sustainable solutions that drive tangible results.",
        ]);

        // ── Core Values ─────────────────────────────────────────────────
        $this->migrator->add('about.core_values', [
            [
                'icon' => 'sparkles',
                'title_id' => 'Presisi Rekayasa',
                'title_en' => 'Engineering Precision',
                'desc_id' => 'Standar kualitas kode tinggi tanpa kompromi, mengedepankan keamanan, performa, dan skalabilitas.',
                'desc_en' => 'Uncompromising code quality with a deep focus on security, performance, and scalability.',
            ],
            [
                'icon' => 'cursor-arrow-rays',
                'title_id' => 'Pengalaman Menyenangkan',
                'title_en' => 'Delightful Experience',
                'desc_id' => 'Setiap produk dirancang agar terasa intuitif, cepat, dan mudah dipahami oleh pengguna.',
                'desc_en' => 'Every product is tailored to feel intuitive, fast, and effortless for end users.',
            ],
            [
                'icon' => 'cube-transparent',
                'title_id' => 'Arsitektur Masa Depan',
                'title_en' => 'Future-Proof Architecture',
                'desc_id' => 'Memilih teknologi yang tepat dan berkelanjutan agar investasi sistem Anda siap menghadapi pertumbuhan.',
                'desc_en' => 'Choosing sustainable modern stacks to ensure your tech investments scale seamlessly.',
            ],
            [
                'icon' => 'users',
                'title_id' => 'Kemitraan Transparan',
                'title_en' => 'Transparent Partnership',
                'desc_id' => 'Kolaborasi jujur, komunikasi terbuka, dan dedikasi penuh dari tahap ide hingga peluncuran.',
                'desc_en' => 'Honest collaboration, clear communication, and dedicated care from ideation to liftoff.',
            ],
        ]);

        // ── Call to Action ──────────────────────────────────────────────
        $this->migrator->add('about.cta_title', [
            'id' => 'Punya ide proyek atau butuh solusi teknologi terpercaya?',
            'en' => 'Have a project in mind or need dependable tech solutions?',
        ]);
        $this->migrator->add('about.cta_subtitle', [
            'id' => 'Mari diskusikan kebutuhan Anda bersama tim RBeverything dan wujudkan sistem digital terbaik.',
            'en' => 'Let us discuss your requirements with the RBeverything team and build an extraordinary digital system together.',
        ]);
        $this->migrator->add('about.cta_button_text', [
            'id' => 'Ayo Berkolaborasi',
            'en' => 'Let’s Collaborate',
        ]);
        $this->migrator->add('about.cta_button_url', 'mailto:hello@rbeverything.com');
    }

    public function down(): void
    {
        $this->migrator->delete('about.stats');
        $this->migrator->delete('about.hero_badge');
        $this->migrator->delete('about.hero_title');
        $this->migrator->delete('about.hero_subtitle');
        $this->migrator->delete('about.story_title');
        $this->migrator->delete('about.story_content');
        $this->migrator->delete('about.vision');
        $this->migrator->delete('about.mission');
        $this->migrator->delete('about.core_values');
        $this->migrator->delete('about.cta_title');
        $this->migrator->delete('about.cta_subtitle');
        $this->migrator->delete('about.cta_button_text');
        $this->migrator->delete('about.cta_button_url');
    }
};
