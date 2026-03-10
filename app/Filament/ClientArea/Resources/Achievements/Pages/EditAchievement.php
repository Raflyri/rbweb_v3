<?php

namespace App\Filament\ClientArea\Resources\Achievements\Pages;

use App\Filament\ClientArea\Resources\Achievements\AchievementResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditAchievement extends EditRecord
{
    protected static string $resource = AchievementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
