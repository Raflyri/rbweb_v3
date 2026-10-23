<?php

namespace App\Filament\Resources\SoftwareCatalogResource\Pages;

use App\Filament\Resources\SoftwareCatalogResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSoftwareCatalog extends CreateRecord
{
    protected static string $resource = SoftwareCatalogResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
