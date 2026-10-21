<?php

namespace App\Filament\Resources\ArcadeGameResource\Pages;

use App\Filament\Resources\ArcadeGameResource;
use Filament\Resources\Pages\CreateRecord;

class CreateArcadeGame extends CreateRecord
{
    protected static string $resource = ArcadeGameResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
