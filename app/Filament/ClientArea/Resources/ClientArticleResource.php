<?php

namespace App\Filament\ClientArea\Resources;

use App\Filament\ClientArea\Resources\ClientArticleResource\Pages;
use App\Models\Article;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ClientArticleResource extends Resource
{
    protected static ?string $model = Article::class;
    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'My Articles';
    protected static ?string $slug            = 'client-articles';
    protected static ?int    $navigationSort  = 5;

    // ── Scope to the authenticated user's articles only ───────────────────
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('user_id', auth()->id());
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Article Content')->schema([
                TextInput::make('title')
                    ->label('Title (English)')
                    ->required()
                    ->maxLength(255)
                    ->live(debounce: 500),

                Textarea::make('content')
                    ->label('Content (English)')
                    ->required()
                    ->rows(12),

                FileUpload::make('thumbnail')
                    ->image()
                    ->disk('public')
                    ->directory('articles/thumbnails')
                    ->imageEditor()
                    ->nullable(),
            ]),

            Section::make('Submission Status')
                ->description('Your article will be reviewed by our team before it goes live.')
                ->schema([
                    Placeholder::make('status')
                        ->label('Current Status')
                        ->content(fn ($record) => $record?->status ?? 'Will be set to Pending Review on save'),
                ])
                ->visibleOn('edit'),
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
