<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LaunchpadLinkResource\Pages;
use App\Models\LaunchpadLink;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Spatie\Permission\Models\Permission;

class LaunchpadLinkResource extends Resource
{
    protected static ?string $model = LaunchpadLink::class;
    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-squares-2x2';
    protected static ?string $navigationLabel = 'Launchpad Links';
    protected static string|\UnitEnum|null $navigationGroup = 'Client Area';
    protected static ?int    $navigationSort  = 10;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Card Details')->columns(2)->schema([
                TextInput::make('title')
                    ->required()
                    ->maxLength(100)
                    ->columnSpan(1),

                TextInput::make('icon')
                    ->label('Heroicon Name')
                    ->placeholder('squares-2x2')
                    ->helperText('Suffix only, e.g. "wrench-screwdriver"')
                    ->required()
                    ->columnSpan(1),

                Textarea::make('description')
                    ->rows(2)
                    ->maxLength(200)
                    ->columnSpanFull(),

                TextInput::make('url')
                    ->label('Target URL')
                    ->url()
                    ->required()
                    ->placeholder('https://base64tools.rbeverything.com')
                    ->columnSpanFull(),
            ]),

            Section::make('Visibility & Access')->columns(2)->schema([
                Select::make('required_permission')
                    ->label('Required Permission')
                    ->helperText('Leave empty to show to all authenticated users.')
                    ->options(fn () => Permission::pluck('name', 'name')->toArray())
                    ->searchable()
                    ->nullable(),

                TextInput::make('sort_order')
                    ->numeric()
                    ->default(0),

                Toggle::make('is_external')
                    ->label('Open in new tab')
                    ->default(true),

                Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sort_order')
                    ->label('#')
                    ->sortable()
                    ->width(50),

                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('url')
                    ->limit(40)
                    ->copyable(),

                TextColumn::make('required_permission')
                    ->label('Permission Gate')
                    ->badge()
                    ->color('warning')
                    ->default('Public'),

                IconColumn::make('is_external')
                    ->label('New Tab')
                    ->boolean(),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),

                TextColumn::make('updated_at')
                    ->since()
                    ->label('Updated'),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')->label('Active'),
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

    public static function getRelationManagers(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListLaunchpadLinks::route('/'),
            'create' => Pages\CreateLaunchpadLink::route('/create'),
            'edit'   => Pages\EditLaunchpadLink::route('/{record}/edit'),
        ];
    }
}
