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
use Filament\Schemas\Components\Grid;
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

            // ── Section 1: Basic card info ───────────────────────────────────
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
                    ->placeholder('https://tools.rbeverything.com/base64')
                    ->columnSpanFull(),
            ]),

            // ── Section 2: Visibility & client-area access ───────────────────
            Section::make('Visibility & Access')->columns(2)->schema([
                Select::make('required_permission')
                    ->label('Required Permission (Client Area)')
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
                    ->label('Active (show in client area dashboard)')
                    ->default(true),
            ]),

            // ── Section 3: Homepage product card ────────────────────────────
            Section::make('Homepage Product Card')
                ->description('Controls whether this link also appears as a product card on the public homepage (/). Only admins can change these settings.')
                ->columns(2)
                ->schema([
                    Toggle::make('show_on_homepage')
                        ->label('Show on Public Homepage')
                        ->helperText('Enable to display this as a product card in the Products section.')
                        ->default(false)
                        ->columnSpanFull()
                        ->live(),

                    TextInput::make('homepage_badge')
                        ->label('Badge / Tag Text')
                        ->placeholder('AI · Computer Vision')
                        ->helperText('Short label shown in the top-left of the card.')
                        ->maxLength(60)
                        ->columnSpan(1),

                    Select::make('homepage_accent')
                        ->label('Accent Colour')
                        ->options([
                            'violet'  => '🟣 Violet',
                            'sky'     => '🔵 Sky Blue',
                            'emerald' => '🟢 Emerald',
                            'rose'    => '🔴 Rose',
                            'amber'   => '🟡 Amber',
                        ])
                        ->default('sky')
                        ->columnSpan(1),

                    TextInput::make('version')
                        ->label('Version Label')
                        ->placeholder('v2.4')
                        ->helperText('Shown in the top-right of the card, e.g. "v2.4".')
                        ->maxLength(20)
                        ->columnSpan(1),

                    TextInput::make('homepage_cta_label')
                        ->label('CTA Button Label')
                        ->placeholder('Open Tool')
                        ->helperText('Text for the card\'s action link.')
                        ->maxLength(40)
                        ->columnSpan(1),

                    Select::make('card_template')
                        ->label('Card Visual Template')
                        ->helperText('Choose the interactive demo shown inside the card.')
                        ->options([
                            'generic'   => '⬜ Generic (description only)',
                            'liveness'  => '👤 Liveness Detection (animated face scan)',
                            'base64'    => '🔤 Base64 Suite (live encoder demo)',
                            'portfolio' => '💼 Portfolio Platform (code window preview)',
                        ])
                        ->default('generic')
                        ->columnSpanFull(),
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

                IconColumn::make('show_on_homepage')
                    ->label('Homepage')
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('gray'),

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
                Tables\Filters\TernaryFilter::make('show_on_homepage')->label('On Homepage'),
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
