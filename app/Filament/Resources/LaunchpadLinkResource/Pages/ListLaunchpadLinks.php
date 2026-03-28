<?php

namespace App\Filament\Resources\LaunchpadLinkResource\Pages;

use App\Filament\Resources\LaunchpadLinkResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLaunchpadLinks extends ListRecords
{
    protected static string $resource = LaunchpadLinkResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
