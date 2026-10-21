<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ArcadeGameResource\Pages;
use App\Models\ArcadeGame;
use App\Policies\ProductPolicy;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ArcadeGameResource extends Resource
{
    protected static ?string $model = ArcadeGame::class;

    protected static ?string $slug = 'arcade-portal';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-puzzle-piece';

    protected static ?string $navigationLabel = 'Arcade Games';

    protected static ?string $modelLabel = 'Game Arcade';

    protected static ?string $pluralModelLabel = 'Arcade Games';

    protected static string|\UnitEnum|null $navigationGroup = 'Konten';

    protected static ?int $navigationSort = 15;

    protected static ?string $recordTitleAttribute = 'title';

    public static function canViewAny(): bool
    {
        return ProductPolicy::userIsManager(auth()->user());
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informasi Game Arcade')
                ->columns(2)
                ->schema([
                    TextInput::make('title')
                        ->label('Judul Game')
                        ->required()
                        ->maxLength(255)
                        ->live(debounce: 500)
                        ->afterStateUpdated(function (Set $set, Get $get, ?string $state) {
                            if (blank($get('slug'))) {
                                $set('slug', Str::slug($state ?? ''));
                            }
                        }),

                    TextInput::make('slug')
                        ->label('Slug URL')
                        ->required()
                        ->maxLength(255)
                        ->unique(ArcadeGame::class, 'slug', ignoreRecord: true),

                    TextInput::make('game_url')
                        ->label('Game URL (Cloudflare / Web URL)')
                        ->url()
                        ->required()
                        ->maxLength(500)
                        ->placeholder('https://tetris-web-rb.pages.dev')
                        ->columnSpanFull(),

                    Textarea::make('description')
                        ->label('Deskripsi Game')
                        ->rows(4)
                        ->required()
                        ->columnSpanFull(),

                    FileUpload::make('thumbnail')
                        ->label('Thumbnail Game')
                        ->image()
                        ->disk('public')
                        ->directory('arcade-thumbnails')
                        ->maxSize(2048),

                    Select::make('status')
                        ->label('Status Publikasi')
                        ->options([
                            ArcadeGame::STATUS_TAYANGKAN => 'Tayangkan',
                            ArcadeGame::STATUS_DRAFT     => 'Draft',
                        ])
                        ->default(ArcadeGame::STATUS_DRAFT)
                        ->required()
                        ->native(false),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('thumbnail')
                    ->label('Thumbnail')
                    ->disk('public')
                    ->square(),

                TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->copyable(),

                TextColumn::make('game_url')
                    ->label('Game URL')
                    ->limit(35)
                    ->copyable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => $state === ArcadeGame::STATUS_TAYANGKAN ? 'success' : 'gray'),

                TextColumn::make('updated_at')
                    ->label('Diubah')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        ArcadeGame::STATUS_TAYANGKAN => 'Tayangkan',
                        ArcadeGame::STATUS_DRAFT     => 'Draft',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListArcadeGames::route('/'),
            'create' => Pages\CreateArcadeGame::route('/create'),
            'edit'   => Pages\EditArcadeGame::route('/{record}/edit'),
        ];
    }
}
