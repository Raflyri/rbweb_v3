<?php

namespace App\Filament\ClientArea\Resources\ClientArticleResource\Pages;

use App\Filament\ClientArea\Resources\ClientArticleResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Actions\Action;

class CreateClientArticle extends CreateRecord
{
    protected static string $resource = ClientArticleResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();

        // If not deliberately set to Draft, default to Pending Review.
        if (($data['status'] ?? '') !== 'Draft') {
            $data['status'] = 'Pending Review';
        }

        return $data;
    }

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction()
                ->label('Create Article'),
            Action::make('saveDraft')
                ->label('Save as Draft')
                ->color('gray')
                ->action(function () {
                    $this->data['status'] = 'Draft';
                    $this->create();
                }),
            $this->getCancelFormAction(),
        ];
    }

    protected function afterCreate(): void
    {
        // Tell Alpine to clear the local storage since we successfully saved to the DB
        $this->dispatch('article-created');

        if ($this->record->status !== 'Draft') {
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
}
