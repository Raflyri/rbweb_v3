<?php

namespace App\Filament\Resources\Articles\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use App\Filament\Support\ArticlePublishRules;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ArticleForm
{
    /**
     * Fill the slug from whichever locale's title the author writes first.
     *
     * An article only needs one language to publish, so nothing here can
     * assume English (or any other locale) is the one that gets filled in.
     * Only acts while the slug is still blank, so it never fights the
     * author once they have typed or customised it themselves.
     */
    protected static function autoSlugFromTitle(): \Closure
    {
        return function (Set $set, Get $get, ?string $state) {
            if (blank($get('slug'))) {
                $set('slug', Str::slug($state ?? ''));
            }
        };
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([

                // ── Translatable Content ──────────────────────────────────────
                // No locale tab is required: an article can be written in as
                // few as one language and still be published (see
                // ArticleContent::publishBlockers, which only requires 50+
                // words in *some* locale, not a specific one).
                Section::make('Content')
                    ->columnSpan(2)
                    ->schema([
                        Tabs::make('Locales')
                            ->contained(false)
                            ->tabs([
                                Tabs\Tab::make('English (EN)')
                                    ->schema([
                                        TextInput::make('title.en')
                                            ->label('Title (English)')
                                            ->live(debounce: 500)
                                            ->afterStateUpdated(static::autoSlugFromTitle())
                                            ->columnSpanFull(),
                                        RichEditor::make('content.en')
                                            ->label('Content (English)')
                                            ->fileAttachmentsDisk('public')
                                            ->fileAttachmentsDirectory('article-attachments')
                                            ->columnSpanFull(),
                                    ]),
                                Tabs\Tab::make('Indonesian (ID)')
                                    ->schema([
                                        TextInput::make('title.id')
                                            ->label('Title (Bahasa Indonesia)')
                                            ->live(debounce: 500)
                                            ->afterStateUpdated(static::autoSlugFromTitle())
                                            ->columnSpanFull(),
                                        RichEditor::make('content.id')
                                            ->label('Content (Bahasa Indonesia)')
                                            ->fileAttachmentsDisk('public')
                                            ->fileAttachmentsDirectory('article-attachments')
                                            ->columnSpanFull(),
                                    ]),
                                Tabs\Tab::make('Malay (MS)')
                                    ->schema([
                                        TextInput::make('title.ms')
                                            ->label('Title (Bahasa Melayu)')
                                            ->live(debounce: 500)
                                            ->afterStateUpdated(static::autoSlugFromTitle())
                                            ->columnSpanFull(),
                                        RichEditor::make('content.ms')
                                            ->label('Content (Bahasa Melayu)')
                                            ->fileAttachmentsDisk('public')
                                            ->fileAttachmentsDirectory('article-attachments')
                                            ->columnSpanFull(),
                                    ]),
                                Tabs\Tab::make('Japanese (JA)')
                                    ->schema([
                                        TextInput::make('title.ja')
                                            ->label('Title (日本語)')
                                            ->live(debounce: 500)
                                            ->afterStateUpdated(static::autoSlugFromTitle())
                                            ->columnSpanFull(),
                                        RichEditor::make('content.ja')
                                            ->label('Content (日本語)')
                                            ->fileAttachmentsDisk('public')
                                            ->fileAttachmentsDirectory('article-attachments')
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                    ]),

                // ── Meta ─────────────────────────────────────────────────────
                Section::make('Meta')
                    ->columnSpan(1)
                    ->schema([
                        TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->helperText('Auto-generated from whichever title you fill in first. Edit to customise.'),

                        Select::make('status')
                            ->options(fn (?\App\Models\Article $record) => ArticlePublishRules::statusOptions($record))
                            ->default('Draft')
                            ->required()
                            ->live()
                            ->rules(ArticlePublishRules::rules())
                            ->afterStateUpdated(fn (Get $get, $state) => ArticlePublishRules::notifyOnStatusChange($get, $state))
                            ->helperText(fn (Get $get): string => ArticlePublishRules::helperText($get)),

                        DateTimePicker::make('published_at')
                            ->label('Publish Date'),
                    ]),

                // ── Thumbnail ────────────────────────────────────────────────
                Section::make('Thumbnail')
                    ->columnSpan(1)
                    ->schema([
                        FileUpload::make('thumbnail')
                            ->image()
                            ->disk('public')
                            ->directory('article-thumbnails')
                            ->imageEditor()
                            ->maxSize(2048)
                            ->helperText('Recommended: 16:9, max 2 MB.'),
                    ]),

            ]);
    }
}
