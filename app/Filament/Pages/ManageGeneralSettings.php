<?php

namespace App\Filament\Pages;

use App\Settings\GeneralSettings;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ManageGeneralSettings extends SettingsPage
{
    protected static string $settings = GeneralSettings::class;

    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationLabel = 'Site Settings';
    protected static string|\UnitEnum|null $navigationGroup = 'System';
    protected static ?int    $navigationSort  = 99;

    // ── Authorization: Super Admin only ───────────────────────────────────────
    public static function canAccess(): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        return $user?->hasRole('super_admin') ?? false;
    }

    public function form(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return $schema->components([

            Tabs::make('Settings')
                ->columnSpanFull()
                ->contained(false)
                ->tabs([

                    // ══════════════════════════════════════════════════════════
                    // TAB 1 — 🎨 Brand Identity
                    // ══════════════════════════════════════════════════════════
                    Tab::make('🎨  Brand Identity')
                        ->schema([

                            Section::make('Brand Name')
                                ->description('The public-facing name of your website.')
                                ->icon('heroicon-o-building-storefront')
                                ->schema([
                                    TextInput::make('site_name')
                                        ->label('Site Name')
                                        ->placeholder('RBeverything')
                                        ->required()
                                        ->maxLength(80)
                                        ->columnSpanFull(),
                                ]),

                            Section::make('Logo & Favicon')
                                ->description('Upload your brand logo and browser favicon. If no logo is uploaded, a text fallback is used. Uploading a new file automatically removes the previous one.')
                                ->icon('heroicon-o-photo')
                                ->columns(2)
                                ->schema([

                                    // ── Site Logo ──────────────────────────
                                    FileUpload::make('site_logo')
                                        ->label('Site Logo')
                                        ->helperText('Recommended: SVG or PNG, transparent background, min 200px wide.')
                                        ->disk('public')
                                        ->directory('settings/logos')
                                        ->image()
                                        ->imageEditor()
                                        ->imagePreviewHeight('120')
                                        ->panelLayout('integrated')
                                        ->acceptedFileTypes(['image/png', 'image/svg+xml', 'image/webp'])
                                        ->maxSize(2048)
                                        ->nullable()
                                        ->deleteUploadedFileUsing(function (?string $file) {
                                            if ($file && Storage::disk('public')->exists($file)) {
                                                Storage::disk('public')->delete($file);
                                            }
                                        })
                                        ->columnSpan(1),

                                    // ── Favicon ────────────────────────────
                                    FileUpload::make('site_favicon')
                                        ->label('Favicon')
                                        ->helperText('Recommended: ICO or 32×32 PNG. Shown in browser tabs.')
                                        ->disk('public')
                                        ->directory('settings/favicons')
                                        ->image()
                                        ->imagePreviewHeight('120')
                                        ->panelLayout('integrated')
                                        ->acceptedFileTypes(['image/x-icon', 'image/png', 'image/svg+xml'])
                                        ->maxSize(512)
                                        ->nullable()
                                        ->deleteUploadedFileUsing(function (?string $file) {
                                            if ($file && Storage::disk('public')->exists($file)) {
                                                Storage::disk('public')->delete($file);
                                            }
                                        })
                                        ->columnSpan(1),
                                ]),

                        ]),

                    // ══════════════════════════════════════════════════════════
                    // TAB 2 — 📞 Contact & Social
                    // ══════════════════════════════════════════════════════════
                    Tab::make('📞  Contact & Social')
                        ->schema([

                            Section::make('Contact Details')
                                ->description('Displayed in hero CTAs, contact sections, and the footer.')
                                ->icon('heroicon-o-chat-bubble-left-ellipsis')
                                ->columns(2)
                                ->schema([

                                    TextInput::make('contact_email')
                                        ->label('Contact Email')
                                        ->email()
                                        ->required()
                                        ->placeholder('hello@rbeverything.com')
                                        ->prefixIcon('heroicon-o-envelope'),

                                    TextInput::make('whatsapp_number')
                                        ->label('WhatsApp Number')
                                        ->tel()
                                        ->required()
                                        ->placeholder('+62 812 3456 7890')
                                        ->helperText('Include country code. Used to generate wa.me links.')
                                        ->prefixIcon('heroicon-o-phone'),
                                ]),

                            Section::make('Social Media Links')
                                ->description('Leave any field blank to hide that social button on the website.')
                                ->icon('heroicon-o-share')
                                ->columns(2)
                                ->schema([

                                    TextInput::make('linkedin_link')
                                        ->label('LinkedIn')
                                        ->url()
                                        ->nullable()
                                        ->placeholder('https://linkedin.com/company/rbeverything')
                                        ->prefixIcon('heroicon-o-link'),

                                    TextInput::make('instagram_link')
                                        ->label('Instagram')
                                        ->url()
                                        ->nullable()
                                        ->placeholder('https://instagram.com/rbeverything')
                                        ->prefixIcon('heroicon-o-link'),

                                    TextInput::make('youtube_link')
                                        ->label('YouTube')
                                        ->url()
                                        ->nullable()
                                        ->placeholder('https://youtube.com/@rbeverything')
                                        ->prefixIcon('heroicon-o-link'),

                                    TextInput::make('twitter_link')
                                        ->label('Twitter / X')
                                        ->url()
                                        ->nullable()
                                        ->placeholder('https://x.com/rbeverything')
                                        ->prefixIcon('heroicon-o-link'),

                                    TextInput::make('github_link')
                                        ->label('GitHub')
                                        ->url()
                                        ->nullable()
                                        ->placeholder('https://github.com/rbeverything')
                                        ->prefixIcon('heroicon-o-code-bracket')
                                        ->columnSpanFull(),
                                ]),

                        ]),

                    // ══════════════════════════════════════════════════════════
                    // TAB 3 — 🌐 Content
                    // ══════════════════════════════════════════════════════════
                    Tab::make('🌐  Content')
                        ->schema([

                            Section::make('Web Tagline')
                                ->description('Displayed as the animated sub-headline in the hero section. Falls back to English for any missing locale.')
                                ->icon('heroicon-o-language')
                                ->schema([

                                    Tabs::make('Locale')
                                        ->contained(false)
                                        ->tabs([
                                            Tab::make('🇬🇧  English')
                                                ->schema([
                                                    TextInput::make('web_tagline.en')
                                                        ->label('Tagline (English)')
                                                        ->required()
                                                        ->placeholder('Your Partner in the Digital Age'),
                                                ]),
                                            Tab::make('🇮🇩  Indonesian')
                                                ->schema([
                                                    TextInput::make('web_tagline.id')
                                                        ->label('Tagline (Bahasa Indonesia)')
                                                        ->placeholder('Mitra Anda di Era Digital'),
                                                ]),
                                            Tab::make('🇲🇾  Malay')
                                                ->schema([
                                                    TextInput::make('web_tagline.ms')
                                                        ->label('Tagline (Bahasa Melayu)')
                                                        ->placeholder('Rakan Anda di Era Digital'),
                                                ]),
                                            Tab::make('🇯🇵  Japanese')
                                                ->schema([
                                                    TextInput::make('web_tagline.ja')
                                                        ->label('Tagline (日本語)')
                                                        ->placeholder('デジタル時代のパートナー'),
                                                ]),
                                        ]),
                                ]),

                        ]),

                    // ══════════════════════════════════════════════════════════
                    // TAB 4 — ⚙️ System
                    // ══════════════════════════════════════════════════════════
                    Tab::make('⚙️  System')
                        ->schema([

                            Section::make('Website URL')
                                ->description('The canonical public URL. Used for absolute links and Open Graph tags.')
                                ->icon('heroicon-o-globe-alt')
                                ->schema([
                                    TextInput::make('frontend_url')
                                        ->label('Frontend URL')
                                        ->url()
                                        ->required()
                                        ->placeholder('https://rbeverything.com')
                                        ->prefixIcon('heroicon-o-globe-alt')
                                        ->columnSpanFull(),
                                ]),

                            Section::make('Danger Zone')
                                ->description('These controls affect all users immediately. Use with caution.')
                                ->icon('heroicon-o-exclamation-triangle')
                                ->schema([
                                    Toggle::make('maintenance_mode')
                                        ->label('Maintenance Mode')
                                        ->helperText('When enabled, a maintenance banner is shown in the client area dashboard.')
                                        ->onColor('danger')
                                        ->offColor('success')
                                        ->inline(false),
                                ]),

                        ]),

                ]),

        ]);
    }
}
