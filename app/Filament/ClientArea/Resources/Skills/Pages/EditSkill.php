<?php

namespace App\Filament\ClientArea\Resources\Skills\Pages;

use App\Filament\ClientArea\Resources\Skills\SkillResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditSkill extends EditRecord
{
    protected static string $resource = SkillResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
