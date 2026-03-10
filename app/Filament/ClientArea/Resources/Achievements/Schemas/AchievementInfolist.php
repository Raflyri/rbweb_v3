<?php

namespace App\Filament\ClientArea\Resources\Achievements\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class AchievementInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('user.name')
                    ->label('User'),
                TextEntry::make('title'),
                TextEntry::make('date')
                    ->date()
                    ->placeholder('-'),
                TextEntry::make('description')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
