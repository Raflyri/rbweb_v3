<?php

namespace App\Filament\Resources\SoftwareCategoryResource\Pages;

use App\Filament\Resources\SoftwareCategoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSoftwareCategory extends CreateRecord
{
    protected static string $resource = SoftwareCategoryResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
