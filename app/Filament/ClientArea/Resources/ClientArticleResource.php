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
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
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
            Grid::make(3)->schema([
                Grid::make(1)->schema([
                    TextInput::make('title')
                        ->label('Title')
                        ->required()
                        ->maxLength(255)
                        ->live(debounce: 500),

                    RichEditor::make('content')
                        ->label('Content')
                        ->required()
                        ->extraInputAttributes(['style' => 'min-height: 400px;'])
                        ->toolbarButtons([
                            'attachFiles', 'blockquote', 'bold', 'bulletList', 'codeBlock',
                            'h2', 'h3', 'italic', 'link', 'orderedList', 'redo',
                            'strike', 'table', 'underline', 'undo',
                        ]),

                    Placeholder::make('estimated_read_time')
                        ->label('Estimated Read Time')
                        ->content(function (Get $get): string {
                            $words = str_word_count(strip_tags($get('content') ?? ''));
                            $minutes = max(1, (int) ceil($words / 200));
                            return "{$minutes} min read (~{$words} words)";
                        }),
                ])->columnSpan(2),

                Grid::make(1)->schema([
                    Select::make('status')
                        ->label('Status')
                        ->options([
                            'Draft' => 'Draft',
                            'Pending Review' => 'Pending Review',
                        ])
                        ->default('Pending Review')
                        ->disabled(fn ($record) => $record?->isPublished() ?? false),

                    DateTimePicker::make('published_at')
                        ->label('Published At')
                        ->nullable(),

                    FileUpload::make('thumbnail')
                        ->label('Featured Image')
                        ->image()
                        ->disk('public')
                        ->directory('articles/thumbnails')
                        ->imageEditor()
                        ->imageEditorAspectRatios(['16:9'])
                        ->nullable(),

                    Select::make('tags')
                        ->multiple()
                        ->relationship('tags', 'name')
                        ->preload()
                        ->createOptionForm([
                            TextInput::make('name')
                                ->required()
                                ->maxLength(255),
                        ]),

                    Textarea::make('meta_description')
                        ->label('SEO Meta Description')
                        ->maxLength(160)
                        ->helperText('Maximum 160 characters for optimal SEO.')
                        ->rows(4)
                        ->nullable(),
                ])->columnSpan(1),
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
