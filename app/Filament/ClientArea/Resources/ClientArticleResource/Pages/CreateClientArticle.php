<?php

namespace App\Filament\ClientArea\Resources\ClientArticleResource\Pages;

use App\Filament\ClientArea\Resources\ClientArticleResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Auth;

class CreateClientArticle extends CreateRecord
{
    protected static string $resource = ClientArticleResource::class;

    // No custom $view in Filament v4 — the Schema handles form+actions natively.
    // We add the CSS scope class via extraAttributes on the page wrapper.
    public function getExtraAttributes(): array
    {
        return ['class' => 'article-editor-page'];
    }

    public function getTagsProperty(): array
    {
        return \App\Models\Tag::all()->pluck('name', 'id')->toArray();
    }

    public function mount(): void
    {
        parent::mount();

        // Initialize translatable fields for the "id" (Indonesian) locale.
        // Use null (not '') for RichEditor — Filament v4's TipTap StateCast
        // crashes when it tries to parse an empty string as a JSON document.
        $this->data['title']            = ['id' => ''];
        $this->data['content']          = ['id' => null];
        $this->data['meta_description'] = ['id' => ''];
        $this->data['status'] = 'Pending Review';
        $this->data['tags']   = [];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::id();

        if (($data['status'] ?? '') !== 'Draft' && ($data['status'] ?? '') !== 'Published') {
            if (!empty($data['published_at']) && strtotime($data['published_at']) > time()) {
                $data['status'] = 'Scheduled';
            } else {
                $data['status'] = 'Pending Review';
            }
        }

        return $data;
    }

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction()
                ->label(fn () => (!empty($this->data['published_at']) && strtotime($this->data['published_at']) > time()) ? 'Schedule Article' : 'Save Article'),
            Action::make('publishNow')
                ->label('Publish Now')
                ->color('success')
                ->action(function () {
                    $this->data['status'] = 'Published';
                    $this->data['published_at'] = now()->format('Y-m-d H:i:s');
                    $this->create();
                }),
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

    protected function getHeaderWidgets(): array { return []; }

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
