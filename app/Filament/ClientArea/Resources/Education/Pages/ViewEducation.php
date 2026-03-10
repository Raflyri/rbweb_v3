<?php

namespace App\Filament\ClientArea\Resources\Education\Pages;

use App\Filament\ClientArea\Resources\Education\EducationResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewEducation extends ViewRecord
{
    protected static string $resource = EducationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
