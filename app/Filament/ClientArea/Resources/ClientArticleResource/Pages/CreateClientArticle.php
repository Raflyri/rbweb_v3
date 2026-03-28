<?php

namespace App\Filament\ClientArea\Resources\ClientArticleResource\Pages;

use App\Filament\ClientArea\Resources\ClientArticleResource;
use Filament\Resources\Pages\CreateRecord;

class CreateClientArticle extends CreateRecord
{
    protected static string $resource = ClientArticleResource::class;

    // Force user_id and status regardless of form input
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();
        $data['status']  = 'Pending Review';

        return $data;
    }

    protected function afterCreate(): void
    {
        // Notify all admins that a new article is pending review
        $admins = \App\Models\User::role(['super_admin', 'admin'])->get();

        foreach ($admins as $admin) {
            \Filament\Notifications\Notification::make()
                ->title('New article awaiting review')
                ->body(auth()->user()->name . ' submitted a new article.')
                ->icon('heroicon-o-document-text')
                ->warning()
                ->sendToDatabase($admin);
        }
    }
}
