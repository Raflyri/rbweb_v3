<?php

namespace App\Filament\ClientArea\Resources\Experiences\Pages;

use App\Filament\ClientArea\Resources\Experiences\ExperienceResource;
use Filament\Resources\Pages\CreateRecord;

class CreateExperience extends CreateRecord
{
    protected static string $resource = ExperienceResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();

        return $data;
    }
}

