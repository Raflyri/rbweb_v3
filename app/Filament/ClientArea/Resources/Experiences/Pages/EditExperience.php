<?php

namespace App\Filament\ClientArea\Resources\Experiences\Pages;

use App\Filament\ClientArea\Resources\Experiences\ExperienceResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditExperience extends EditRecord
{
    protected static string $resource = ExperienceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
