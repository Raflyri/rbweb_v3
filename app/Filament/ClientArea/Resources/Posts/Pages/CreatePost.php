<?php

namespace App\Filament\ClientArea\Resources\Posts\Pages;

use App\Filament\ClientArea\Resources\Posts\PostResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePost extends CreateRecord
{
    protected static string $resource = PostResource::class;
}
