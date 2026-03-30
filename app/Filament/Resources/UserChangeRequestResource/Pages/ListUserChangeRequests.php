<?php

namespace App\Filament\Resources\UserChangeRequestResource\Pages;

use App\Filament\Resources\UserChangeRequestResource;
use Filament\Resources\Pages\ListRecords;

class ListUserChangeRequests extends ListRecords
{
    protected static string $resource = UserChangeRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [];   // Super Admin reviews requests here — no manual create
    }
}
