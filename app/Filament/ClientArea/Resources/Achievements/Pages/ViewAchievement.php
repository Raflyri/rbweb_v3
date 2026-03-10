<?php

namespace App\Filament\ClientArea\Resources\Achievements\Pages;

use App\Filament\ClientArea\Resources\Achievements\AchievementResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewAchievement extends ViewRecord
{
    protected static string $resource = AchievementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
