<?php

namespace App\Filament\ClientArea\Resources\Education\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class EducationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                TextInput::make('institution')
                    ->required(),
                TextInput::make('degree')
                    ->required(),
                DatePicker::make('start_date')
                    ->required(),
                DatePicker::make('end_date'),
            ]);
    }
}
