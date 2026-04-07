<?php

namespace App\Filament\ClientArea\Resources;

use App\Filament\ClientArea\Resources\ClientArticleResource\Pages;
use App\Models\Article;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Infolists\Components\TextEntry;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Tabs;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ClientArticleResource extends Resource
{
    protected static ?string $model = Article::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'My Articles';
    protected static ?string $slug = 'client-articles';
    protected static ?int $navigationSort = 5;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('user_id', Auth::id());
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Group::make()
                ->extraAttributes([
                    'class' => 'flex flex-col gap-8 w-full',
                    'x-data' => "{ activeTab: 'id' }"
                ])
                ->columnSpanFull()
                ->schema([
                    // ── Main writing area (Top) ─────────────────────────
                    Section::make()
                        ->extraAttributes(['class' => 'article-editor-main min-w-0'])
                        ->schema([
                            // Language Tabs View
                            \Filament\Schemas\Components\View::make('filament.components.locale-tabs'),

                            // Featured image / thumbnail (16:9)
                            FileUpload::make('thumbnail')
                                ->hiddenLabel()
                                ->image()
                                ->disk('public')
                                ->directory('articles/thumbnails')
                                ->imageEditor()
                                ->imageEditorAspectRatioOptions(['16:9' => '16:9 (Landscape)'])
                                ->nullable(),

                            // Build Dynamic Lokale Inputs
                            ...array_map(function ($langCode) {
                                return Group::make()
                                    ->extraAttributes([
                                        'x-show' => "activeTab === '{$langCode}'",
                                        'style' => $langCode === 'id' ? '' : 'display: none;',
                                    ])
                                    ->schema([
                                        TextInput::make("title.{$langCode}")
                                            ->hiddenLabel()
                                            ->placeholder('Judul artikel Anda ('. strtoupper($langCode) .')...')
                                            ->required($langCode === 'id') // Only require ID
                                            ->maxLength(255)
                                            ->live(debounce: 500)
                                            ->extraAttributes(['class' => 'article-title-field'])
                                            ->extraInputAttributes([
                                                'class' => 'text-3xl font-bold bg-transparent border-0 ring-0 focus:ring-0 px-0 shadow-none',
                                                'style' => 'box-shadow: none;',
                                            ]),

                                        RichEditor::make("content.{$langCode}")
                                            ->hiddenLabel()
                                            ->placeholder('Mulai menulis artikel Anda di sini ('. strtoupper($langCode) .')...')
                                            ->required($langCode === 'id')
                                            ->extraInputAttributes([
                                                'style' => 'min-height: 560px; box-shadow: none; max-width: 100%;',
                                            ])
                                            ->toolbarButtons([
                                                'attachFiles', 'blockquote', 'bold', 'bulletList', 'codeBlock',
                                                'h2', 'h3', 'italic', 'link', 'orderedList', 'redo',
                                                'strike', 'table', 'underline', 'undo',
                                            ]),
                                    ]);
                            }, ['id', 'my', 'en', 'jp']),
                        ]),

                    // ── Settings Area (Bottom) ──────────────────────────
                    Section::make('Pengaturan & Properti Artikel')
                        ->schema([
                            Grid::make(3)
                                ->schema([
                                    TextEntry::make('author')
                                        ->label('Penulis')
                                        ->state(fn () => Auth::user()?->name ?? 'Unknown')
                                        ->helperText('Editor'),

                                    Select::make('status')
                                        ->label('Status')
                                        ->options([
                                            'Draft'          => 'Draft',
                                            'Pending Review' => 'Pending Review',
                                            'Scheduled'      => 'Scheduled',
                                            'Published'      => 'Published',
                                        ])
                                        ->default('Pending Review'),

                                    DateTimePicker::make('published_at')
                                        ->label('Jadwal Publish')
                                        ->native(false)
                                        ->displayFormat('d/m/Y H:i')
                                        ->placeholder('Pilih tanggal')
                                        ->nullable(),
                                ]),

                            Grid::make(2)
                                ->schema([
                                    Select::make('tags')
                                        ->label('Kategori / Tag')
                                        ->multiple()
                                        ->relationship('tags', 'name')
                                        ->preload()
                                        ->createOptionForm([
                                            TextInput::make('name')
                                                ->required()
                                                ->placeholder('Nama tag baru...')
                                                ->maxLength(255),
                                        ])
                                        ->placeholder('+ Tambah tag'),

                                    TextEntry::make('estimated_read_time')
                                        ->label('Estimasi Membaca')
                                        ->state(function (?Article $record): string {
                                            if (! $record) {
                                                return '0 kata · ~1 mnt baca';
                                            }
                                            $text = is_string($record->getRawOriginal('content'))
                                                ? strip_tags($record->getRawOriginal('content'))
                                                : strip_tags((string) $record->content);
                                            $words   = $text !== '' ? str_word_count($text) : 0;
                                            $minutes = max(1, (int) ceil($words / 200));
                                            return "{$words} kata · ~{$minutes} mnt baca";
                                        }),
                                ]),
                        ]),

                    // ── SEO Area ───────────────────────────────
                    Section::make('Search Engine Optimization (SEO)')
                        ->collapsed()
                        ->schema([
                            ...array_map(function ($langCode) {
                                return Group::make()
                                    ->extraAttributes([
                                        'x-show' => "activeTab === '{$langCode}'",
                                        'style' => $langCode === 'id' ? '' : 'display: none;',
                                    ])
                                    ->schema([
                                        Textarea::make("meta_description.{$langCode}")
                                            ->label('Meta Description ('. strtoupper($langCode) .')')
                                            ->placeholder('Panduan lengkap tren desain UI tahun 2025...')
                                            ->maxLength(160)
                                            ->live(debounce: 500)
                                            ->helperText(fn ($state) => mb_strlen(is_string($state) ? $state : '') . ' / 160')
                                            ->rows(3)
                                            ->nullable(),
                                    ]);
                            }, ['id', 'my', 'en', 'jp']),
                        ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Title')
                    ->searchable()
                    ->limit(60)
                    ->tooltip(fn ($record) => $record->getTranslation('title', 'en', true)),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Published'      => 'success',
                        'Pending Review' => 'warning',
                        'Draft'          => 'gray',
                        default          => 'gray',
                    }),

                TextColumn::make('created_at')
                    ->label('Submitted')
                    ->since()
                    ->sortable(),

                TextColumn::make('reviewed_at')
                    ->label('Reviewed')
                    ->since()
                    ->placeholder('Not reviewed yet')
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make(),

                ViewAction::make()
                    ->visible(fn (Article $record) => ! $record->isDraft()),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListClientArticles::route('/'),
            'create' => Pages\CreateClientArticle::route('/create'),
            'edit'   => Pages\EditClientArticle::route('/{record}/edit'),
        ];
    }
}
