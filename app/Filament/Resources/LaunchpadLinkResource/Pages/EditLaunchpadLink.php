<?php

namespace App\Filament\Resources\LaunchpadLinkResource\Pages;

use App\Filament\Resources\LaunchpadLinkResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLaunchpadLink extends EditRecord
{
    protected static string $resource = LaunchpadLinkResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
