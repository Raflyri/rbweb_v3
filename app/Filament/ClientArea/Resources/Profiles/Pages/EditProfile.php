<?php

namespace App\Filament\ClientArea\Resources\Profiles\Pages;

use App\Filament\ClientArea\Resources\Profiles\ProfileResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditProfile extends EditRecord
{
    protected static string $resource = ProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
