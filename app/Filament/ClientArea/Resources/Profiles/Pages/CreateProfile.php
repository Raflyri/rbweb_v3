<?php

namespace App\Filament\ClientArea\Resources\Profiles\Pages;

use App\Filament\ClientArea\Resources\Profiles\ProfileResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProfile extends CreateRecord
{
    protected static string $resource = ProfileResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();

        return $data;
    }
}

