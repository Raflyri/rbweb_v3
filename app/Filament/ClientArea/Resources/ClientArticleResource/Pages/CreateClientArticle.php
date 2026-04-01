<?php

namespace App\Filament\ClientArea\Resources\ClientArticleResource\Pages;

use App\Filament\ClientArea\Resources\ClientArticleResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Auth;

class CreateClientArticle extends CreateRecord
{
    protected static string $resource = ClientArticleResource::class;
    
    protected string $view = 'filament.client-area.articles.article-editor';

    public function getTagsProperty(): array
    {
        return \App\Models\Tag::all()->pluck('name', 'id')->toArray();
    }

    public function mount(): void
    {
        parent::mount();

        // Initialize translatable fields for the "id" (Indonesian) locale
        // so clicking create doesn't error on missing keys.
        $this->data['title'] = ['id' => ''];
        $this->data['content'] = ['id' => ''];
        $this->data['meta_description'] = ['id' => ''];
        $this->data['status'] = 'Pending Review';
        $this->data['tags'] = [];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::id();

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
                    ->body((Auth::user()?->name ?? 'Someone') . ' submitted a new article.')
                    ->icon('heroicon-o-document-text')
                    ->warning()
                    ->sendToDatabase($admin);
            }
        }
    }
}
