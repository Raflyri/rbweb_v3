<?php

namespace App\Filament\ClientArea\Resources\Experiences\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class ExperienceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                TextInput::make('company')
                    ->required(),
                TextInput::make('role')
                    ->required(),
                DatePicker::make('start_date')
                    ->required(),
                DatePicker::make('end_date'),
                Textarea::make('description')
                    ->columnSpanFull(),
            ]);
    }
}
