<?php

namespace App\Filament\ClientArea\Resources\Experiences;

use App\Filament\ClientArea\Resources\Experiences\Pages\CreateExperience;
use App\Filament\ClientArea\Resources\Experiences\Pages\EditExperience;
use App\Filament\ClientArea\Resources\Experiences\Pages\ListExperiences;
use App\Filament\ClientArea\Resources\Experiences\Pages\ViewExperience;
use App\Filament\ClientArea\Resources\Experiences\Schemas\ExperienceForm;
use App\Filament\ClientArea\Resources\Experiences\Schemas\ExperienceInfolist;
use App\Filament\ClientArea\Resources\Experiences\Tables\ExperiencesTable;
use App\Models\Experience;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ExperienceResource extends Resource
{
    protected static ?string $model = Experience::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'company';

    public static function form(Schema $schema): Schema
    {
        return ExperienceForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ExperienceInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ExperiencesTable::configure($table);
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
            'index' => ListExperiences::route('/'),
            'create' => CreateExperience::route('/create'),
            'view' => ViewExperience::route('/{record}'),
            'edit' => EditExperience::route('/{record}/edit'),
        ];
    }
}
