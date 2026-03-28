<?php

namespace App\Filament\Resources\Articles\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ArticleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([

                // ── Translatable Content ──────────────────────────────────────
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
                                            ->required()
                                            ->live(debounce: 500)
                                            ->afterStateUpdated(function (Set $set, ?string $state) {
                                                $set('slug', Str::slug($state ?? ''));
                                            })
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
                            ->helperText('Auto-generated from English title. Edit to customise.'),

                        Select::make('status')
                            ->options([
                                'Draft'          => 'Draft',
                                'Pending Review' => 'Pending Review',
                                'Published'      => 'Published',
                            ])
                            ->default('Draft')
                            ->required(),

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
