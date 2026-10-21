<?php

namespace App\Filament\Resources\ArcadeGameResource\Pages;

use App\Filament\Resources\ArcadeGameResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditArcadeGame extends EditRecord
{
    protected static string $resource = ArcadeGameResource::class;

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
