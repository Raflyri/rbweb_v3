<?php

namespace App\Filament\Pages;

use App\Settings\AboutSettings;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class ManageAboutSettings extends SettingsPage
{
    protected static string $settings = AboutSettings::class;

    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-information-circle';
    protected static ?string $navigationLabel = 'Tentang Kami';
    protected static string|\UnitEnum|null $navigationGroup = 'Konten';
    protected static ?int    $navigationSort  = 2;

    // ── Authorization: super_admin and admin ──────────────────────────────────
    public static function canAccess(): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        return $user?->hasAnyRole(['super_admin', 'admin']) ?? false;
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([

            Tabs::make('AboutPageSettings')
                ->columnSpanFull()
                ->contained(false)
                ->tabs([

                    // ══════════════════════════════════════════════════════════
                    // TAB 1 — 📊 Statistik (Stats Grid)
                    // ══════════════════════════════════════════════════════════
                    Tab::make('📊  Statistik Grid')
                        ->schema([
                            Section::make('Statistik Pencapaian')
                                ->description('Nilai dan label statistik yang tampil di homepage dan halaman Tentang Kami.')
                                ->icon('heroicon-o-chart-bar')
                                ->schema([
                                    Repeater::make('stats')
                                        ->label('Item Statistik')
                                        ->addActionLabel('Tambah Item Statistik')
                                        ->schema([
                                            TextInput::make('value')
                                                ->label('Nilai / Angka')
                                                ->placeholder('Contoh: 10+, 3+, ∞, 99%')
                                                ->required()
                                                ->columnSpan(1),
                                            TextInput::make('label_id')
                                                ->label('Label (Bahasa Indonesia)')
                                                ->placeholder('Contoh: Produk diluncurkan')
                                                ->required()
                                                ->columnSpan(1),
                                            TextInput::make('label_en')
                                                ->label('Label (English)')
                                                ->placeholder('Contoh: Products shipped')
                                                ->required()
                                                ->columnSpan(1),
                                        ])
                                        ->columns(3)
                                        ->reorderable()
                                        ->columnSpanFull(),
                                ]),
                        ]),

                    // ══════════════════════════════════════════════════════════
                    // TAB 2 — 🚀 Hero Section
                    // ══════════════════════════════════════════════════════════
                    Tab::make('🚀  Hero Section')
                        ->schema([
                            Section::make('Header Utama Halaman')
                                ->description('Teks judul besar dan pengantar di bagian atas halaman Tentang Kami.')
                                ->icon('heroicon-o-sparkles')
                                ->schema([
                                    Tabs::make('HeroLocale')
                                        ->contained(false)
                                        ->tabs([
                                            Tab::make('🇮🇩  Indonesian')
                                                ->schema([
                                                    TextInput::make('hero_badge.id')
                                                        ->label('Badge / Label')
                                                        ->placeholder('Tentang RBeverything')
                                                        ->required(),
                                                    TextInput::make('hero_title.id')
                                                        ->label('Judul Utama')
                                                        ->placeholder('Kami percaya teknologi seharusnya terasa mudah')
                                                        ->required(),
                                                    Textarea::make('hero_subtitle.id')
                                                        ->label('Sub-judul / Ringkasan')
                                                        ->rows(3)
                                                        ->required(),
                                                ]),
                                            Tab::make('🇬🇧  English')
                                                ->schema([
                                                    TextInput::make('hero_badge.en')
                                                        ->label('Badge / Label (English)')
                                                        ->placeholder('About RBeverything')
                                                        ->required(),
                                                    TextInput::make('hero_title.en')
                                                        ->label('Main Title (English)')
                                                        ->placeholder('We believe technology should feel effortless')
                                                        ->required(),
                                                    Textarea::make('hero_subtitle.en')
                                                        ->label('Subtitle (English)')
                                                        ->rows(3)
                                                        ->required(),
                                                ]),
                                        ]),
                                ]),
                        ]),

                    // ══════════════════════════════════════════════════════════
                    // TAB 3 — 📖 Cerita & Filosofi
                    // ══════════════════════════════════════════════════════════
                    Tab::make('📖  Cerita & Filosofi')
                        ->schema([
                            Section::make('Narasi Studio')
                                ->description('Penjelasan detail mengenai latar belakang, dedikasi, dan pendekatan kerja RBeverything.')
                                ->icon('heroicon-o-book-open')
                                ->schema([
                                    Tabs::make('StoryLocale')
                                        ->contained(false)
                                        ->tabs([
                                            Tab::make('🇮🇩  Indonesian')
                                                ->schema([
                                                    TextInput::make('story_title.id')
                                                        ->label('Judul Cerita')
                                                        ->required(),
                                                    RichEditor::make('story_content.id')
                                                        ->label('Konten Cerita')
                                                        ->required(),
                                                ]),
                                            Tab::make('🇬🇧  English')
                                                ->schema([
                                                    TextInput::make('story_title.en')
                                                        ->label('Story Title (English)')
                                                        ->required(),
                                                    RichEditor::make('story_content.en')
                                                        ->label('Story Content (English)')
                                                        ->required(),
                                                ]),
                                        ]),
                                ]),
                        ]),

                    // ══════════════════════════════════════════════════════════
                    // TAB 4 — 🎯 Visi & Misi
                    // ══════════════════════════════════════════════════════════
                    Tab::make('🎯  Visi & Misi')
                        ->schema([
                            Section::make('Arah & Tujuan')
                                ->description('Pernyataan visi jangka panjang dan misi operasional.')
                                ->icon('heroicon-o-flag')
                                ->schema([
                                    Tabs::make('VisionMissionLocale')
                                        ->contained(false)
                                        ->tabs([
                                            Tab::make('🇮🇩  Indonesian')
                                                ->schema([
                                                    Textarea::make('vision.id')
                                                        ->label('Pernyataan Visi')
                                                        ->rows(3)
                                                        ->required(),
                                                    Textarea::make('mission.id')
                                                        ->label('Pernyataan Misi')
                                                        ->helperText('Gunakan nomor baris baru untuk setiap poin misi.')
                                                        ->rows(5)
                                                        ->required(),
                                                ]),
                                            Tab::make('🇬🇧  English')
                                                ->schema([
                                                    Textarea::make('vision.en')
                                                        ->label('Vision Statement (English)')
                                                        ->rows(3)
                                                        ->required(),
                                                    Textarea::make('mission.en')
                                                        ->label('Mission Statement (English)')
                                                        ->helperText('Use line breaks for each mission point.')
                                                        ->rows(5)
                                                        ->required(),
                                                ]),
                                        ]),
                                ]),
                        ]),

                    // ══════════════════════════════════════════════════════════
                    // TAB 5 — 💎 Nilai Utama (Core Values)
                    // ══════════════════════════════════════════════════════════
                    Tab::make('💎  Nilai Utama')
                        ->schema([
                            Section::make('Prinsip Fundamental (Core Values)')
                                ->description('Prinsip yang memandu setiap karya dan interaksi di RBeverything.')
                                ->icon('heroicon-o-shield-check')
                                ->schema([
                                    Repeater::make('core_values')
                                        ->label('Daftar Nilai')
                                        ->addActionLabel('Tambah Nilai Utama')
                                        ->schema([
                                            Select::make('icon')
                                                ->label('Ikon')
                                                ->options([
                                                    'sparkles' => '✨ Sparkles (Inovasi / Presisi)',
                                                    'cursor-arrow-rays' => '🎯 Cursor / Target (Pengalaman / Fokus)',
                                                    'cube-transparent' => '🧊 Cube (Arsitektur / Struktur)',
                                                    'users' => '👥 Users (Kemitraan / Kolaborasi)',
                                                    'shield-check' => '🛡️ Shield Check (Keamanan / Kualitas)',
                                                    'bolt' => '⚡ Bolt (Kecepatan / Ketangkasan)',
                                                    'heart' => '❤️ Heart (Kepedulian)',
                                                ])
                                                ->default('sparkles')
                                                ->columnSpanFull(),
                                            TextInput::make('title_id')
                                                ->label('Judul Nilai (ID)')
                                                ->required(),
                                            TextInput::make('title_en')
                                                ->label('Title (EN)')
                                                ->required(),
                                            Textarea::make('desc_id')
                                                ->label('Deskripsi (ID)')
                                                ->rows(2)
                                                ->required(),
                                            Textarea::make('desc_en')
                                                ->label('Description (EN)')
                                                ->rows(2)
                                                ->required(),
                                        ])
                                        ->columns(2)
                                        ->reorderable()
                                        ->columnSpanFull(),
                                ]),
                        ]),

                    // ══════════════════════════════════════════════════════════
                    // TAB 6 — 📣 Call To Action (CTA)
                    // ══════════════════════════════════════════════════════════
                    Tab::make('📣  Call To Action')
                        ->schema([
                            Section::make('Ajakan Kolaborasi')
                                ->description('Bagian penutup di bawah halaman untuk mendorong pengunjung memulai proyek bersama.')
                                ->icon('heroicon-o-chat-bubble-bottom-center-text')
                                ->schema([
                                    Tabs::make('CtaLocale')
                                        ->contained(false)
                                        ->tabs([
                                            Tab::make('🇮🇩  Indonesian')
                                                ->schema([
                                                    TextInput::make('cta_title.id')
                                                        ->label('Judul Ajakan (ID)')
                                                        ->required(),
                                                    Textarea::make('cta_subtitle.id')
                                                        ->label('Sub-judul / Deskripsi (ID)')
                                                        ->rows(2)
                                                        ->required(),
                                                    TextInput::make('cta_button_text.id')
                                                        ->label('Teks Tombol (ID)')
                                                        ->required(),
                                                ]),
                                            Tab::make('🇬🇧  English')
                                                ->schema([
                                                    TextInput::make('cta_title.en')
                                                        ->label('CTA Title (EN)')
                                                        ->required(),
                                                    Textarea::make('cta_subtitle.en')
                                                        ->label('CTA Subtitle (EN)')
                                                        ->rows(2)
                                                        ->required(),
                                                    TextInput::make('cta_button_text.en')
                                                        ->label('Button Text (EN)')
                                                        ->required(),
                                                ]),
                                        ]),
                                    TextInput::make('cta_button_url')
                                        ->label('Target URL / Link Tombol')
                                        ->placeholder('mailto:hello@rbeverything.com atau https://wa.me/...')
                                        ->required()
                                        ->columnSpanFull(),
                                ]),
                        ]),

                ]),

        ]);
    }
}
