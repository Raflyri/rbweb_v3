<?php

namespace App\Filament\Resources\ArcadeGameResource\Pages;

use App\Filament\Resources\ArcadeGameResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListArcadeGames extends ListRecords
{
    protected static string $resource = ArcadeGameResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Tambah Game Arcade'),
        ];
    }
}
