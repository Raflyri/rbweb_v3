<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SoftwareCategoryResource\Pages;
use App\Models\SoftwareCategory;
use App\Policies\ProductPolicy;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class SoftwareCategoryResource extends Resource
{
    protected static ?string $model = SoftwareCategory::class;

    protected static ?string $slug = 'software-categories';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationLabel = 'Kategori Software';

    protected static ?string $modelLabel = 'Kategori Software';

    protected static ?string $pluralModelLabel = 'Kategori Software';

    protected static string|\UnitEnum|null $navigationGroup = 'Konten';

    protected static ?int $navigationSort = 17;

    protected static ?string $recordTitleAttribute = 'name';

    public static function canViewAny(): bool
    {
        return ProductPolicy::userIsManager(auth()->user());
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Detail Kategori Software')
                ->description('Kategori dinamis untuk mengelompokkan software di portal katalog.')
                ->columns(2)
                ->schema([
                    TextInput::make('name')
                        ->label('Nama Kategori Software')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('Contoh: EdTech / Sekolah, Enterprise / Retail')
                        ->live(debounce: 500)
                        ->afterStateUpdated(function (Set $set, Get $get, ?string $state) {
                            if (blank($get('slug'))) {
                                $set('slug', Str::slug($state ?? ''));
                            }
                        }),

                    TextInput::make('slug')
                        ->label('Slug Kategori')
                        ->required()
                        ->maxLength(255)
                        ->unique(SoftwareCategory::class, 'slug', ignoreRecord: true),

                    Textarea::make('description')
                        ->label('Deskripsi Kategori (Opsional)')
                        ->rows(3)
                        ->columnSpanFull(),

                    TextInput::make('sort_order')
                        ->label('Urutan Tampil')
                        ->numeric()
                        ->default(0),
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
                    ->copyable()
                    ->fontFamily('mono'),

                TextColumn::make('software_count')
                    ->counts('software')
                    ->label('Jumlah Software')
                    ->badge()
                    ->color('info'),

                TextColumn::make('sort_order')
                    ->label('Urutan')
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->label('Terakhir Diubah')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            'index'  => Pages\ListSoftwareCategories::route('/'),
            'create' => Pages\CreateSoftwareCategory::route('/create'),
            'edit'   => Pages\EditSoftwareCategory::route('/{record}/edit'),
        ];
    }
}
