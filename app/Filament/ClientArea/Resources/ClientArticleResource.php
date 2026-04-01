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
use Filament\Forms\Components\Placeholder;
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
        return parent::getEloquentQuery()->where('user_id', auth()->id());
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(3)
                ->extraAttributes(function (\Livewire\Component $livewire) {
                    if ($livewire instanceof Pages\CreateClientArticle) {
                        return [
                            'x-data' => '{
                                init() {
                                    let key = "draft_article_" + ' . auth()->id() . ';
                                    let saved = localStorage.getItem(key);
                                    if (saved) {
                                        try {
                                            let parsed = JSON.parse(saved);
                                            if (!$wire.data.title && parsed.title) {
                                                $wire.set("data", { ...$wire.data, ...parsed });
                                            }
                                        } catch(e) {}
                                    }
                                    $watch("data", value => {
                                        localStorage.setItem(key, JSON.stringify(value));
                                    });
                                    $wire.on("article-created", () => {
                                        localStorage.removeItem(key);
                                    });
                                }
                            }'
                        ];
                    }
                    return [];
                })
                ->schema([
                    Group::make()->schema([
                        Section::make()->schema([
                            FileUpload::make('thumbnail')
                                ->hiddenLabel()
                                ->image()
                                ->disk('public')
                                ->directory('articles/thumbnails')
                                ->imageEditor()
                                ->imageEditorAspectRatios(['16:9'])
                                ->nullable()
                                ->extraAttributes(['class' => 'mb-4']),

                            Select::make('tags')
                                ->hiddenLabel()
                                ->multiple()
                                ->relationship('tags', 'name')
                                ->preload()
                                ->createOptionForm([
                                    TextInput::make('name')
                                        ->required()
                                        ->placeholder('Enter tag name...')
                                        ->maxLength(255),
                                ])
                                ->placeholder('+ Tag Kategori')
                                ->extraAttributes(['class' => 'mb-4']),

                            TextInput::make('title')
                                ->hiddenLabel()
                                ->placeholder('Judul artikel Anda...')
                                ->required()
                                ->maxLength(255)
                                ->live(debounce: 500)
                                ->extraInputAttributes(['class' => 'text-2xl md:text-3xl font-bold bg-transparent border-0 ring-0 focus:ring-0 px-0 shadow-none border-transparent', 'style' => 'box-shadow: none;']),

                            RichEditor::make('content')
                                ->hiddenLabel()
                                ->placeholder('Write your article content here...')
                                ->required()
                                ->extraInputAttributes(['style' => 'min-height: 500px; box-shadow: none; max-width: 100%;'])
                                ->toolbarButtons([
                                    'attachFiles', 'blockquote', 'bold', 'bulletList', 'codeBlock',
                                    'h2', 'h3', 'italic', 'link', 'orderedList', 'redo',
                                    'strike', 'table', 'underline', 'undo',
                                ]),
                        ])
                    ])->columnSpan(['sm' => 3, 'lg' => 2]),

                    Group::make()->schema([
                        Tabs::make('Tabs')
                            ->tabs([
                                Tabs\Tab::make('Properti')
                                    ->schema([
                                        Placeholder::make('author')
                                            ->label('Penulis')
                                            ->content(fn () => auth()->user()->name ?? 'Unknown')
                                            ->helperText('Editor'),

                                        DateTimePicker::make('published_at')
                                            ->label('Jadwal Publish')
                                            ->native(false)
                                            ->displayFormat('d/m/Y H:i')
                                            ->placeholder('Pilih tanggal')
                                            ->nullable(),

                                        Select::make('status')
                                            ->label('Status')
                                            ->options([
                                                'Draft' => 'Draft',
                                                'Pending Review' => 'Pending Review',
                                            ])
                                            ->default('Pending Review')
                                            ->disabled(fn ($record) => $record?->isPublished() ?? false),

                                        Placeholder::make('estimated_read_time')
                                            ->label('Estimasi Membaca')
                                            ->content(function (Get $get): string {
                                                $words = str_word_count(strip_tags($get('content') ?? ''));
                                                $minutes = max(1, (int) ceil($words / 200));
                                                return "{$words} kata · ~{$minutes} mnt baca";
                                            }),
                                    ]),

                                Tabs\Tab::make('SEO')
                                    ->schema([
                                        Textarea::make('meta_description')
                                            ->label('Meta Description')
                                            ->placeholder('Panduan lengkap tren desain UI tahun 2025...')
                                            ->maxLength(160)
                                            ->live(debounce: 500)
                                            ->helperText(fn ($state) => mb_strlen($state ?? '') . ' / 160')
                                            ->rows(4)
                                            ->nullable(),
                                    ]),
                            ]),
                    ])->columnSpan(['sm' => 3, 'lg' => 1]),
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
                        'Published' => 'success',
                        'Pending Review' => 'warning',
                        'Draft' => 'gray',
                        default => 'gray',
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
            'index' => Pages\ListClientArticles::route('/'),
            'create' => Pages\CreateClientArticle::route('/create'),
            'edit' => Pages\EditClientArticle::route('/{record}/edit'),
        ];
    }
}
