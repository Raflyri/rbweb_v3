<?php

namespace App\Filament\Resources\Articles\Schemas;

use App\Filament\Support\ArticleFields;
use App\Filament\Support\ArticlePublishRules;
use App\Models\Article;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class ArticleForm
{
    /** [locale => Tab label] — order also drives display order. */
    protected const LOCALE_TABS = [
        'en' => 'English (EN)',
        'id' => 'Indonesian (ID)',
        'ms' => 'Malay (MS)',
        'ja' => 'Japanese (JA)',
    ];

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([

                // ── Translatable Content ──────────────────────────────────────
                // No locale tab is required: an article can be written in as
                // few as one language and still be published (see
                // ArticleContent::publishBlockers, which only requires 50+
                // words in *some* locale, not a specific one). Each tab also
                // carries that language's SEO fields, so switching locale
                // keeps title/content/meta together instead of scattering
                // them across the form.
                Section::make('Content')
                    ->columnSpan(2)
                    ->schema([
                        Tabs::make('Locales')
                            ->contained(false)
                            ->tabs(
                                collect(self::LOCALE_TABS)->map(
                                    fn (string $label, string $locale) => Tabs\Tab::make($label)
                                        ->schema([
                                            ArticleFields::titleField($locale, "Title ({$label})")
                                                ->live(debounce: 500)
                                                ->afterStateUpdated(ArticleFields::autoSlugFromTitle())
                                                ->columnSpanFull(),
                                            ArticleFields::contentField($locale, "Content ({$label})")
                                                ->columnSpanFull(),
                                            ArticleFields::metaTitleField($locale, "Meta Title ({$label})")
                                                ->columnSpanFull(),
                                            ArticleFields::metaDescriptionField($locale, "Meta Description ({$label})")
                                                ->columnSpanFull(),
                                        ]),
                                )->values()->all(),
                            ),
                    ]),

                // ── Meta ─────────────────────────────────────────────────────
                Section::make('Meta')
                    ->columnSpan(1)
                    ->schema([
                        ArticleFields::slugField()
                            ->helperText('Auto-generated from whichever title you fill in first. Edit to customise.'),

                        ArticleFields::typeField(),

                        Select::make('status')
                            ->options(fn (?Article $record) => ArticlePublishRules::statusOptions($record))
                            ->default('Draft')
                            ->required()
                            ->live()
                            ->rules(ArticlePublishRules::rules())
                            ->afterStateUpdated(fn (Get $get, $state) => ArticlePublishRules::notifyOnStatusChange($get, $state))
                            ->helperText(fn (Get $get): string => ArticlePublishRules::helperText($get)),

                        ArticleFields::publishedAtField(),
                    ]),

                // ── Thumbnail & Tags ────────────────────────────────────────
                Section::make('Thumbnail & Tags')
                    ->columnSpan(1)
                    ->schema([
                        ArticleFields::thumbnailField(),

                        Select::make('tags')
                            ->label('Tags')
                            ->multiple()
                            ->relationship('tags', 'name')
                            ->preload()
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                            ])
                            ->hintIcon(
                                'heroicon-o-question-mark-circle',
                                'Opsional — topik/kategori untuk membantu pengelompokan dan pencarian. Boleh pilih lebih dari satu atau buat tag baru.',
                            ),
                    ]),

            ]);
    }
}
