<?php

namespace App\Filament\Resources\SoftwareCatalogResource\Pages;

use App\Filament\Resources\SoftwareCatalogResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSoftwareCatalog extends EditRecord
{
    protected static string $resource = SoftwareCatalogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
