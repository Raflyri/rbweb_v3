<?php

namespace App\Filament\ClientArea\Resources\Education;

use App\Filament\ClientArea\Resources\Education\Pages\CreateEducation;
use App\Filament\ClientArea\Resources\Education\Pages\EditEducation;
use App\Filament\ClientArea\Resources\Education\Pages\ListEducation;
use App\Filament\ClientArea\Resources\Education\Pages\ViewEducation;
use App\Filament\ClientArea\Resources\Education\Schemas\EducationForm;
use App\Filament\ClientArea\Resources\Education\Schemas\EducationInfolist;
use App\Filament\ClientArea\Resources\Education\Tables\EducationTable;
use App\Models\Education;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class EducationResource extends Resource
{
    protected static ?string $model = Education::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'institution';

    public static function form(Schema $schema): Schema
    {
        return EducationForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return EducationInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EducationTable::configure($table);
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
            'index' => ListEducation::route('/'),
            'create' => CreateEducation::route('/create'),
            'view' => ViewEducation::route('/{record}'),
            'edit' => EditEducation::route('/{record}/edit'),
        ];
    }
}
