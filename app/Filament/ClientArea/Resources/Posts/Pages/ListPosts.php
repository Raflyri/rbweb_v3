<?php

namespace App\Filament\ClientArea\Resources\Posts\Pages;

use App\Filament\ClientArea\Resources\Posts\PostResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPosts extends ListRecords
{
    protected static string $resource = PostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
