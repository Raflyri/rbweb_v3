<?php

namespace App\Filament\ClientArea\Resources\ClientArticleResource\Pages;

use App\Filament\ClientArea\Resources\ClientArticleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListClientArticles extends ListRecords
{
    protected static string $resource = ClientArticleResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
