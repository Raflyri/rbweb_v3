<?php

namespace App\Filament\ClientArea\Resources\Achievements\Pages;

use App\Filament\ClientArea\Resources\Achievements\AchievementResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAchievements extends ListRecords
{
    protected static string $resource = AchievementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
