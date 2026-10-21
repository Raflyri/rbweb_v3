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
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
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

                    Select::make('game_category_id')
                        ->label('Kategori Game')
                        ->relationship('category', 'name')
                        ->searchable()
                        ->preload()
                        ->createOptionForm([
                            TextInput::make('name')
                                ->label('Nama Kategori')
                                ->required()
                                ->maxLength(255)
                                ->live(debounce: 500)
                                ->afterStateUpdated(function (Set $set, ?string $state) {
                                    $set('slug', Str::slug($state ?? ''));
                                }),
                            TextInput::make('slug')
                                ->label('Slug Kategori')
                                ->required()
                                ->maxLength(255)
                                ->unique('game_categories', 'slug'),
                        ])
                        ->createOptionAction(fn ($action) => $action->modalHeading('Tambah Kategori Game'))
                        ->placeholder('Pilih atau buat kategori game baru...')
                        ->native(false),

                    Select::make('status')
                        ->label('Status Publikasi')
                        ->options([
                            ArcadeGame::STATUS_TAYANGKAN => 'Tayangkan',
                            ArcadeGame::STATUS_DRAFT     => 'Draft',
                        ])
                        ->default(ArcadeGame::STATUS_DRAFT)
                        ->required()
                        ->native(false),

                    FileUpload::make('thumbnail')
                        ->label('Thumbnail Game')
                        ->image()
                        ->disk('public')
                        ->directory('arcade-thumbnails')
                        ->maxSize(2048),

                    Toggle::make('is_spotlight')
                        ->label('Tampilkan sebagai Spotlight Game')
                        ->helperText('Jika diaktifkan, game ini akan ditampilkan di banner utama Spotlight pada halaman Arcade.')
                        ->default(false),
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

                TextColumn::make('category.name')
                    ->label('Kategori')
                    ->badge()
                    ->color('info')
                    ->sortable()
                    ->placeholder('-'),

                IconColumn::make('is_spotlight')
                    ->label('Spotlight')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('game_url')
                    ->label('Game URL')
                    ->limit(30)
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
                SelectFilter::make('game_category_id')
                    ->label('Kategori')
                    ->relationship('category', 'name'),

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
