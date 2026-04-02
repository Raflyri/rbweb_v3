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
            Grid::make(3)
                ->schema([
                    // ── Main writing area (2/3 width) ─────────────────────────
                    Group::make()
                        ->extraAttributes(['class' => 'article-editor-main'])
                        ->schema([
                            Section::make()->schema([

                                // Featured image / thumbnail (16:9)
                                FileUpload::make('thumbnail')
                                    ->hiddenLabel()
                                    ->image()
                                    ->disk('public')
                                    ->directory('articles/thumbnails')
                                    ->imageEditor()
                                    ->imageEditorAspectRatioOptions(['16:9' => '16:9 (Landscape)'])
                                    ->nullable(),

                                // Article title (styled as a document heading via CSS)
                                TextInput::make('title')
                                    ->hiddenLabel()
                                    ->placeholder('Judul artikel Anda...')
                                    ->required()
                                    ->maxLength(255)
                                    ->live(debounce: 500)
                                    ->extraAttributes(['class' => 'article-title-field'])
                                    ->extraInputAttributes([
                                        'class' => 'text-3xl font-bold bg-transparent border-0 ring-0 focus:ring-0 px-0 shadow-none',
                                        'style' => 'box-shadow: none;',
                                    ]),

                                // Rich content editor
                                RichEditor::make('content')
                                    ->hiddenLabel()
                                    ->placeholder('Mulai menulis artikel Anda di sini...')
                                    ->required()
                                    ->extraInputAttributes([
                                        'style' => 'min-height: 560px; box-shadow: none; max-width: 100%;',
                                    ])
                                    ->toolbarButtons([
                                        'attachFiles', 'blockquote', 'bold', 'bulletList', 'codeBlock',
                                        'h2', 'h3', 'italic', 'link', 'orderedList', 'redo',
                                        'strike', 'table', 'underline', 'undo',
                                    ]),

                            ]),
                        ])
                        ->columnSpan(['sm' => 3, 'lg' => 2]),

                    // ── Sidebar meta area (1/3 width) ──────────────────────────
                    Group::make()
                        ->extraAttributes(['class' => 'article-editor-sidebar'])
                        ->schema([
                            Tabs::make('Tabs')
                                ->tabs([
                                    // ── Properti tab ──────────────────────────
                                    Tabs\Tab::make('Properti')
                                        ->schema([
                                            TextEntry::make('author')
                                                ->label('Penulis')
                                                ->state(fn () => Auth::user()?->name ?? 'Unknown')
                                                ->helperText('Editor')
                                                ->columnSpanFull(),

                                            Select::make('status')
                                                ->label('Status')
                                                ->options([
                                                    'Draft'          => 'Draft',
                                                    'Pending Review' => 'Pending Review',
                                                ])
                                                ->default('Pending Review')
                                                ->disabled(fn ($record) => $record?->isPublished() ?? false),

                                            DateTimePicker::make('published_at')
                                                ->label('Jadwal Publish')
                                                ->native(false)
                                                ->displayFormat('d/m/Y H:i')
                                                ->placeholder('Pilih tanggal')
                                                ->nullable(),

                                            // Tags (moved from main column into sidebar for cleaner writing area)
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
                                                    // On edit: read from the already-saved record (safe, no TipTap cast).
                                                    // On create: $record is null — show placeholder.
                                                    if (! $record) {
                                                        return '0 kata · ~1 mnt baca';
                                                    }
                                                    $text = is_string($record->getRawOriginal('content'))
                                                        ? strip_tags($record->getRawOriginal('content'))
                                                        : strip_tags((string) $record->content);
                                                    $words   = $text !== '' ? str_word_count($text) : 0;
                                                    $minutes = max(1, (int) ceil($words / 200));
                                                    return "{$words} kata · ~{$minutes} mnt baca";
                                                })
                                                ->columnSpanFull(),
                                        ]),

                                    // ── SEO tab ───────────────────────────────
                                    Tabs\Tab::make('SEO')
                                        ->schema([
                                            Textarea::make('meta_description')
                                                ->label('Meta Description')
                                                ->placeholder('Panduan lengkap tren desain UI tahun 2025...')
                                                ->maxLength(160)
                                                ->live(debounce: 500)
                                                ->helperText(fn ($state) => mb_strlen(is_string($state) ? $state : '') . ' / 160')
                                                ->rows(4)
                                                ->nullable(),
                                        ]),
                                ]),
                        ])
                        ->columnSpan(['sm' => 3, 'lg' => 1]),
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
                EditAction::make()
                    ->visible(fn (Article $record) => $record->isDraft()),

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
