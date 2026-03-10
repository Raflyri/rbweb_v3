<?php

namespace App\Filament\ClientArea\Resources\Education\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class EducationInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('user.name')
                    ->label('User'),
                TextEntry::make('institution'),
                TextEntry::make('degree'),
                TextEntry::make('start_date')
                    ->date(),
                TextEntry::make('end_date')
                    ->date()
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
