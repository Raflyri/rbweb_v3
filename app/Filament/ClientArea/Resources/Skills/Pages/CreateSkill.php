<?php

namespace App\Filament\ClientArea\Resources\Skills\Pages;

use App\Filament\ClientArea\Resources\Skills\SkillResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSkill extends CreateRecord
{
    protected static string $resource = SkillResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();

        return $data;
    }
}

