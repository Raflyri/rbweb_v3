<?php

namespace App\Filament\Support;

use Filament\Actions\Action;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\RichEditor\EditorCommand;
use Filament\Forms\Components\RichEditor\Plugins\Contracts\RichContentPlugin;
use Filament\Forms\Components\RichEditor\RichEditorTool;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\View;
use Filament\Support\Enums\Width;

class ArticleEditorEnhancementsPlugin implements RichContentPlugin
{
    public static function make(): static
    {
        return app(static::class);
    }

    /**
     * @return array<\Tiptap\Core\Extension>
     */
    public function getTipTapPhpExtensions(): array
    {
        return [];
    }

    /**
     * @return array<string>
     */
    public function getTipTapJsExtensions(): array
    {
        return [];
    }

    /**
     * @return array<RichEditorTool>
     */
    public function getEditorTools(): array
    {
        return [
            RichEditorTool::make('insertEmoji')
                ->label('Sisipkan Emoji')
                ->action('insertEmoji')
                ->icon('heroicon-o-face-smile'),

            RichEditorTool::make('insertTablePreset')
                ->label('Preset Tabel')
                ->action('insertTablePreset')
                ->icon('heroicon-o-table-cells'),

            RichEditorTool::make('insertCallout')
                ->label('Kotak Catatan (Callout)')
                ->action('insertCallout')
                ->icon('heroicon-o-chat-bubble-bottom-center-text'),
        ];
    }

    /**
     * @return array<Action>
     */
    public function getEditorActions(): array
    {
        return [
            // ── 1. Emoji Picker Action ──────────────────────────────────────────
            Action::make('insertEmoji')
                ->label('Sisipkan Emoji')
                ->modalHeading('Pilih & Sisipkan Emoji')
                ->modalDescription('Pilih emoji dari daftar di bawah atau ketik langsung emoji yang diinginkan.')
                ->modalWidth(Width::Medium)
                ->modalSubmitActionLabel('Sisipkan Emoji')
                ->schema([
                    TextInput::make('emoji')
                        ->label('Emoji Terpilih')
                        ->required()
                        ->placeholder('Klik salah satu emoji di bawah...')
                        ->extraInputAttributes([
                            'data-emoji-input' => 'true',
                            'class' => 'text-center text-2xl font-bold tracking-wider',
                        ]),

                    View::make('filament.components.emoji-picker-modal'),
                ])
                ->action(function (array $arguments, array $data, RichEditor $component): void {
                    if (blank($data['emoji'] ?? null)) {
                        return;
                    }

                    $component->runCommands(
                        [
                            EditorCommand::make('insertContent', arguments: [$data['emoji']]),
                        ],
                        editorSelection: $arguments['editorSelection'] ?? null,
                    );
                }),

            // ── 2. Table Presets Action ─────────────────────────────────────────
            Action::make('insertTablePreset')
                ->label('Preset Tabel')
                ->modalHeading('Sisipkan Preset Tabel Siap Pakai')
                ->modalDescription('Pilih format tabel terstruktur sesuai kebutuhan artikel Anda.')
                ->modalWidth(Width::Large)
                ->modalSubmitActionLabel('Sisipkan Tabel')
                ->schema([
                    Select::make('preset')
                        ->label('Pilihan Format Tabel')
                        ->options([
                            'comparison'    => '⭐ Tabel Perbandingan Fitur (Fitur, Starter, Pro, Enterprise)',
                            'specification' => '📋 Tabel Spesifikasi Teknis (Parameter / Fitur, Spesifikasi, Keterangan)',
                            'pro_con'       => '⚖️ Tabel Pro & Kontra (Kelebihan vs Kekurangan)',
                            'pricing'       => '💳 Tabel Paket Layanan & Harga (Paket, Cocok Untuk, Benefit, Biaya)',
                            'timeline'      => '📅 Tabel Timeline & Roadmap (Fase / Waktu, Target / Agenda, Output)',
                        ])
                        ->default('comparison')
                        ->required(),

                    Select::make('rows')
                        ->label('Jumlah Baris Data')
                        ->options([
                            '2' => '2 Baris Data',
                            '3' => '3 Baris Data (Standar)',
                            '4' => '4 Baris Data',
                            '5' => '5 Baris Data',
                        ])
                        ->default('3')
                        ->required(),
                ])
                ->action(function (array $arguments, array $data, RichEditor $component): void {
                    $preset = $data['preset'] ?? 'comparison';
                    $rows = (int) ($data['rows'] ?? 3);

                    $tableHtml = match ($preset) {
                        'comparison'    => self::buildComparisonTableHtml($rows),
                        'specification' => self::buildSpecTableHtml($rows),
                        'pro_con'       => self::buildProConTableHtml($rows),
                        'pricing'       => self::buildPricingTableHtml($rows),
                        'timeline'      => self::buildTimelineTableHtml($rows),
                        default         => self::buildComparisonTableHtml($rows),
                    };

                    $component->runCommands(
                        [
                            EditorCommand::make('insertContent', arguments: [$tableHtml]),
                        ],
                        editorSelection: $arguments['editorSelection'] ?? null,
                    );
                }),

            // ── 3. Callout Box Action ───────────────────────────────────────────
            Action::make('insertCallout')
                ->label('Kotak Catatan')
                ->modalHeading('Sisipkan Kotak Catatan (Callout)')
                ->modalDescription('Buat kotak sorotan khusus untuk tips, informasi, atau peringatan.')
                ->modalWidth(Width::Large)
                ->modalSubmitActionLabel('Sisipkan Kotak')
                ->schema([
                    Select::make('type')
                        ->label('Tipe Kotak Catatan')
                        ->options([
                            'info'    => '💡 Info & Catatan Penting (Biru / Netral)',
                            'tip'     => '✅ Tips & Rekomendasi (Hijau)',
                            'warning' => '⚠️ Perhatian & Peringatan (Kuning / Amber)',
                            'quote'   => '💬 Kutipan / Highlight Kunci (Merah Brand RBeverything)',
                        ])
                        ->default('info')
                        ->required(),

                    TextInput::make('title')
                        ->label('Judul Catatan (Opsional)')
                        ->placeholder('Contoh: Catatan Penting atau Tips Tambahan'),

                    Textarea::make('content')
                        ->label('Isi Pesan Catatan')
                        ->required()
                        ->rows(3)
                        ->placeholder('Tuliskan pesan penjelasan atau tips di sini...'),
                ])
                ->action(function (array $arguments, array $data, RichEditor $component): void {
                    $type = $data['type'] ?? 'info';
                    $title = trim($data['title'] ?? '');
                    $content = trim($data['content'] ?? '');

                    if ($content === '') {
                        return;
                    }

                    $icon = match ($type) {
                        'tip'     => '✅',
                        'warning' => '⚠️',
                        'quote'   => '💬',
                        default   => '💡',
                    };

                    $heading = $title !== '' ? "{$icon} " . e($title) : "{$icon} " . ucfirst($type);
                    $html = "<blockquote><p><strong>{$heading}</strong><br/>" . nl2br(e($content)) . "</p></blockquote><p></p>";

                    $component->runCommands(
                        [
                            EditorCommand::make('insertContent', arguments: [$html]),
                        ],
                        editorSelection: $arguments['editorSelection'] ?? null,
                    );
                }),
        ];
    }

    /* ── Table Generator Helpers ───────────────────────────────────────── */

    protected static function buildComparisonTableHtml(int $rows): string
    {
        $headers = ['Fitur / Kriteria', 'Starter', 'Professional', 'Enterprise'];
        $sampleRows = [
            ['Dukungan Cloud & Backup', 'Harian', 'Real-time', 'Multi-region Active'],
            ['Kapasitas Pengguna', 'Hingga 5 User', 'Hingga 25 User', 'Unlimited'],
            ['Akses API & Integrasi', 'Dasar (100 req/mnt)', 'Penuh (1.000 req/mnt)', 'Dedicated Gateway'],
            ['SLA Garansi Uptime', '99.5%', '99.9%', '99.99% Enterprise'],
            ['Prioritas Support', 'Email (1x24 jam)', 'Chat & Email (2 jam)', '24/7 Dedicated Support'],
        ];

        $html = '<table class="article-table article-table--comparison"><thead><tr>';
        foreach ($headers as $h) {
            $html .= '<th style="text-align: left;">' . e($h) . '</th>';
        }
        $html .= '</tr></thead><tbody>';

        for ($i = 0; $i < $rows; $i++) {
            $r = $sampleRows[$i % count($sampleRows)];
            $html .= '<tr>';
            $html .= '<td><strong>' . e($r[0]) . '</strong></td>';
            $html .= '<td>' . e($r[1]) . '</td>';
            $html .= '<td>' . e($r[2]) . '</td>';
            $html .= '<td>' . e($r[3]) . '</td>';
            $html .= '</tr>';
        }

        $html .= '</tbody></table><p></p>';
        return $html;
    }

    protected static function buildSpecTableHtml(int $rows): string
    {
        $headers = ['Parameter / Fitur', 'Spesifikasi Teknis', 'Keterangan'];
        $sampleRows = [
            ['Arsitektur Sistem', 'Microservices & Event-Driven', 'Terskalakan horizontal dengan zero-downtime'],
            ['Database Engine', 'PostgreSQL 16 + Redis Cluster', 'Dilengkapi replikasi failover otomatis'],
            ['Protokol Komunikasi', 'RESTful API & WebSocket Secure', 'Enkripsi TLS 1.3 standar industri'],
            ['Autentikasi & Keamanan', 'OAuth 2.0 + 2FA / Passkey', 'Audit log aktivitas pengguna tersimpan'],
            ['Infrastruktur Deployment', 'Docker + Kubernetes Container', 'Terintegrasi CI/CD automated test pipeline'],
        ];

        $html = '<table class="article-table article-table--spec"><thead><tr>';
        foreach ($headers as $h) {
            $html .= '<th style="text-align: left;">' . e($h) . '</th>';
        }
        $html .= '</tr></thead><tbody>';

        for ($i = 0; $i < $rows; $i++) {
            $r = $sampleRows[$i % count($sampleRows)];
            $html .= '<tr>';
            $html .= '<td><strong>' . e($r[0]) . '</strong></td>';
            $html .= '<td><code>' . e($r[1]) . '</code></td>';
            $html .= '<td>' . e($r[2]) . '</td>';
            $html .= '</tr>';
        }

        $html .= '</tbody></table><p></p>';
        return $html;
    }

    protected static function buildProConTableHtml(int $rows): string
    {
        $sampleRows = [
            ['Performa sangat cepat dan efisien resource', 'Memerlukan kurva belajar awal bagi pemula'],
            ['Ekosistem modular dan mudah diperluas', 'Dokumentasi untuk integrasi edge cukup teknis'],
            ['Keamanan bawaan tingkat enterprise', 'Perlu konfigurasi dedicated server untuk skala besar'],
            ['Hemat biaya operasional jangka panjang', 'Setup awal memerlukan panduan arsitek sistem'],
            ['Kompatibilitas tinggi dengan sistem modern', 'Memerlukan maintenance berkala'],
        ];

        $html = '<table class="article-table article-table--procon"><thead><tr>';
        $html .= '<th style="text-align: left; width: 50%;">✅ Kelebihan & Keunggulan</th>';
        $html .= '<th style="text-align: left; width: 50%;">⚠️ Kekurangan & Catatan</th>';
        $html .= '</tr></thead><tbody>';

        for ($i = 0; $i < $rows; $i++) {
            $r = $sampleRows[$i % count($sampleRows)];
            $html .= '<tr>';
            $html .= '<td>✅ ' . e($r[0]) . '</td>';
            $html .= '<td>⚠️ ' . e($r[1]) . '</td>';
            $html .= '</tr>';
        }

        $html .= '</tbody></table><p></p>';
        return $html;
    }

    protected static function buildPricingTableHtml(int $rows): string
    {
        $headers = ['Paket Layanan', 'Target Pengguna', 'Fasilitas & Benefit Utama', 'Estimasi Biaya'];
        $sampleRows = [
            ['Standard Support', 'Bisnis Pemula / UMKM', 'Setup sistem dasar, panduan instalasi, support email jam kerja', 'Mulai Rp 1.5jt/bln'],
            ['Business Growth', 'Perusahaan Menengah', 'Optimasi performa, integrasi payment & AI, SLA support 4 jam', 'Mulai Rp 4.5jt/bln'],
            ['Custom Enterprise', 'Korporasi & Institusi', 'Dedicated engineer, arsitektur khusus, SLA 99.99%, audit security', 'Hubungi Sales'],
        ];

        $html = '<table class="article-table article-table--pricing"><thead><tr>';
        foreach ($headers as $h) {
            $html .= '<th style="text-align: left;">' . e($h) . '</th>';
        }
        $html .= '</tr></thead><tbody>';

        for ($i = 0; $i < $rows; $i++) {
            $r = $sampleRows[$i % count($sampleRows)];
            $html .= '<tr>';
            $html .= '<td><strong>' . e($r[0]) . '</strong></td>';
            $html .= '<td>' . e($r[1]) . '</td>';
            $html .= '<td>' . e($r[2]) . '</td>';
            $html .= '<td><strong>' . e($r[3]) . '</strong></td>';
            $html .= '</tr>';
        }

        $html .= '</tbody></table><p></p>';
        return $html;
    }

    protected static function buildTimelineTableHtml(int $rows): string
    {
        $headers = ['Fase / Waktu', 'Agenda Kegiatan', 'Target Deliverable / Output', 'Status'];
        $sampleRows = [
            ['Minggu 1', 'Discovery & Requirement Gathering', 'Dokumen spesifikasi teknis & wireframe', '✅ Selesai'],
            ['Minggu 2 - 3', 'Core Engine & Database Development', 'Prototype backend teruji & arsitektur data', '🔄 Berjalan'],
            ['Minggu 4', 'Frontend UI/UX & Testing Integration', 'Aplikasi siap UAT & staging deploy', '⏳ Rencana'],
            ['Minggu 5', 'Security Hardening & Production Launch', 'Sistem live dengan monitoring real-time', '⏳ Rencana'],
        ];

        $html = '<table class="article-table article-table--timeline"><thead><tr>';
        foreach ($headers as $h) {
            $html .= '<th style="text-align: left;">' . e($h) . '</th>';
        }
        $html .= '</tr></thead><tbody>';

        for ($i = 0; $i < $rows; $i++) {
            $r = $sampleRows[$i % count($sampleRows)];
            $html .= '<tr>';
            $html .= '<td><strong>' . e($r[0]) . '</strong></td>';
            $html .= '<td>' . e($r[1]) . '</td>';
            $html .= '<td>' . e($r[2]) . '</td>';
            $html .= '<td>' . e($r[3]) . '</td>';
            $html .= '</tr>';
        }

        $html .= '</tbody></table><p></p>';
        return $html;
    }
}
