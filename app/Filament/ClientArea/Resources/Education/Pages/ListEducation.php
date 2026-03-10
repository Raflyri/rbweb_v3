<?php

namespace App\Filament\ClientArea\Resources\Education\Pages;

use App\Filament\ClientArea\Resources\Education\EducationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEducation extends ListRecords
{
    protected static string $resource = EducationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
