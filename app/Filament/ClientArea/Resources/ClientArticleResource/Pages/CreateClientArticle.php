<?php

namespace App\Filament\ClientArea\Resources\ClientArticleResource\Pages;

use App\Filament\ClientArea\Resources\ClientArticleResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Actions\Action;
use App\Policies\ArticlePolicy;
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

        $status    = $data['status'] ?? '';
        $canPublish = ArticlePolicy::userCanPublish(Auth::user());

        // A client may only ever land on Draft or Pending Review. Anything
        // else they submit — including a tampered Livewire payload asking for
        // Published — is folded back into the review queue.
        if (! $canPublish && in_array($status, ArticlePolicy::PUBLIC_STATUSES, true)) {
            $status = 'Pending Review';
        }

        if ($status !== 'Draft' && $status !== 'Published') {
            $scheduledForLater = ! empty($data['published_at'])
                && strtotime($data['published_at']) > time();

            $status = ($scheduledForLater && $canPublish) ? 'Scheduled' : 'Pending Review';
        }

        $data['status'] = $status;

        return $data;
    }

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction()
                ->label(fn () => (
                    ArticlePolicy::userCanPublish(Auth::user())
                    && ! empty($this->data['published_at'])
                    && strtotime($this->data['published_at']) > time()
                ) ? 'Schedule Article' : 'Save Article'),
            Action::make('publishNow')
                ->label('Publish Now')
                ->color('success')
                ->visible(fn () => ArticlePolicy::userCanPublish(Auth::user()))
                ->action(function () {
                    $this->data['status'] = 'Published';
                    // Respect a date the author already picked (backdating a
                    // historical post is a legitimate use case) — only
                    // default to right now when they left the field empty.
                    // blank(), not ??=: Filament leaves an untouched date
                    // field as '' rather than null.
                    if (blank($this->data['published_at'] ?? null)) {
                        $this->data['published_at'] = now()->format('Y-m-d H:i:s');
                    }
                    $this->create();
                    
                    \Filament\Notifications\Notification::make()
                        ->success()
                        ->title('Berhasil diterbitkan')
                        ->body('Artikel Anda telah bersatus Published.')
                        ->send();
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
