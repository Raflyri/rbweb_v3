<?php

namespace App\Filament\ClientArea\Resources\Achievements;

use App\Filament\ClientArea\Resources\Achievements\Pages\CreateAchievement;
use App\Filament\ClientArea\Resources\Achievements\Pages\EditAchievement;
use App\Filament\ClientArea\Resources\Achievements\Pages\ListAchievements;
use App\Filament\ClientArea\Resources\Achievements\Pages\ViewAchievement;
use App\Filament\ClientArea\Resources\Achievements\Schemas\AchievementForm;
use App\Filament\ClientArea\Resources\Achievements\Schemas\AchievementInfolist;
use App\Filament\ClientArea\Resources\Achievements\Tables\AchievementsTable;
use App\Models\Achievement;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AchievementResource extends Resource
{
    protected static ?string $model = Achievement::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return AchievementForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AchievementInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AchievementsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAchievements::route('/'),
            'create' => CreateAchievement::route('/create'),
            'view' => ViewAchievement::route('/{record}'),
            'edit' => EditAchievement::route('/{record}/edit'),
        ];
    }
}
