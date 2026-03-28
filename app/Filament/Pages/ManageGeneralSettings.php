<?php

namespace App\Filament\Pages;

use App\Settings\GeneralSettings;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;

class ManageGeneralSettings extends SettingsPage
{
    protected static string $settings = GeneralSettings::class;

    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationLabel = 'Site Settings';
    protected static string|\UnitEnum|null $navigationGroup = 'System';
    protected static ?int    $navigationSort  = 99;

    public static function canAccess(): bool
    {
        /** @var \App\Models\User|null $user */
        $user = auth()->user();

        return $user?->hasRole('super_admin') ?? false;
    }

    public function form(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return $schema
            ->components([

                // ── Contact Details ──────────────────────────────────────────
                Section::make('Contact Details')
                    ->description('Displayed on the homepage and used in CTAs.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('whatsapp_number')
                            ->label('WhatsApp Number')
                            ->placeholder('+62 812 3456 7890')
                            ->tel()
                            ->required(),

                        TextInput::make('contact_email')
                            ->label('Contact Email')
                            ->email()
                            ->placeholder('hello@rbeverything.com')
                            ->required(),
                    ]),

                // ── Social Links ─────────────────────────────────────────────
                Section::make('Social Links')
                    ->description('URLs for social media profiles in the footer.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('linkedin_link')
                            ->label('LinkedIn URL')
                            ->url()
                            ->placeholder('https://linkedin.com/company/rbeverything')
                            ->required(),

                        TextInput::make('instagram_link')
                            ->label('Instagram URL')
                            ->url()
                            ->placeholder('https://instagram.com/rbeverything')
                            ->required(),
                    ]),

                // ── Tagline (per locale) ──────────────────────────────────────
                Section::make('Web Tagline')
                    ->description('Translatable tagline stored as JSON per locale. Falls back to English if a locale is empty.')
                    ->schema([
                        Tabs::make('Locale')
                            ->contained(false)
                            ->tabs([
                                Tabs\Tab::make('English (EN)')
                                    ->schema([
                                        TextInput::make('web_tagline.en')
                                            ->label('Tagline (English)')
                                            ->required()
                                            ->placeholder('Your Partner in the Digital Age'),
                                    ]),
                                Tabs\Tab::make('Indonesian (ID)')
                                    ->schema([
                                        TextInput::make('web_tagline.id')
                                            ->label('Tagline (Bahasa Indonesia)')
                                            ->placeholder('Mitra Anda di Era Digital'),
                                    ]),
                                Tabs\Tab::make('Malay (MS)')
                                    ->schema([
                                        TextInput::make('web_tagline.ms')
                                            ->label('Tagline (Bahasa Melayu)')
                                            ->placeholder('Rakan Anda di Era Digital'),
                                    ]),
                                Tabs\Tab::make('Japanese (JA)')
                                    ->schema([
                                        TextInput::make('web_tagline.ja')
                                            ->label('Tagline (日本語)')
                                            ->placeholder('デジタル時代のパートナー'),
                                    ]),
                            ]),
                    ]),

                // ── System Controls ───────────────────────────────────────────
                Section::make('System Controls')
                    ->description('Control the public-facing website URL and maintenance mode.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('frontend_url')
                            ->label('Frontend URL')
                            ->url()
                            ->placeholder('https://rbeverything.com')
                            ->columnSpanFull()
                            ->required(),

                        Toggle::make('maintenance_mode')
                            ->label('Maintenance Mode')
                            ->helperText('When enabled, a maintenance banner is shown in the client area.')
                            ->inline(false),
                    ]),

            ]);
    }
}
