<?php

namespace App\Filament\ClientArea\Resources\Posts\Pages;

use App\Filament\ClientArea\Resources\Posts\PostResource;
use App\Models\Post;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreatePost extends CreateRecord
{
    protected static string $resource = PostResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // ✅ Override paksa: user_id dari session, bukan dari form input
        // Ini mencegah user memanipulasi hidden field user_id
        $data['user_id'] = Auth::id();
        $data['status']  = $data['status'] ?? Post::STATUS_DRAFT;

        return $data;
    }
}
