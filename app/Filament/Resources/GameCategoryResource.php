<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GameCategoryResource\Pages;
use App\Models\GameCategory;
use App\Policies\ProductPolicy;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class GameCategoryResource extends Resource
{
    protected static ?string $model = GameCategory::class;

    protected static ?string $slug = 'game-categories';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $navigationLabel = 'Kategori Game';

    protected static ?string $modelLabel = 'Kategori Game';

    protected static ?string $pluralModelLabel = 'Kategori Game';

    protected static string|\UnitEnum|null $navigationGroup = 'Konten';

    protected static ?int $navigationSort = 16;

    protected static ?string $recordTitleAttribute = 'name';

    public static function canViewAny(): bool
    {
        return ProductPolicy::userIsManager(auth()->user());
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Detail Kategori Game')
                ->columns(2)
                ->schema([
                    TextInput::make('name')
                        ->label('Nama Kategori Game')
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
                        ->unique(GameCategory::class, 'slug', ignoreRecord: true),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Kategori')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->copyable(),

                TextColumn::make('games_count')
                    ->label('Jumlah Game')
                    ->counts('games')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->label('Diubah')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(),
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
            'index'  => Pages\ListGameCategories::route('/'),
            'create' => Pages\CreateGameCategory::route('/create'),
            'edit'   => Pages\EditGameCategory::route('/{record}/edit'),
        ];
    }
}
