<?php

namespace App\Filament\ClientArea\Resources\ClientArticleResource\Pages;

use App\Filament\ClientArea\Resources\ClientArticleResource;
use App\Support\ArticleLocale;
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

        // Initialize every locale, not just "id" — Livewire's entangle
        // (used by RichEditor and any ->live() field, e.g. meta_title/
        // meta_description's character counters) needs the key to already
        // exist in the component's data array before the browser can bind
        // to it. Leaving en/ms/ja unset threw "property cannot be found on
        // component" for those tabs and corrupted the page's Livewire AJAX
        // payload badly enough that every subsequent Livewire request on
        // the page — including completely unrelated components like the
        // email verification banner's "Resend" button — failed outright
        // with net::ERR_CONNECTION_CLOSED.
        //
        // content uses null (not '') per locale — Filament v4's TipTap
        // StateCast crashes when it tries to parse an empty string as a
        // JSON document.
        $this->data['title']            = array_fill_keys(ArticleLocale::SUPPORTED, '');
        $this->data['content']          = array_fill_keys(ArticleLocale::SUPPORTED, null);
        $this->data['meta_title']       = array_fill_keys(ArticleLocale::SUPPORTED, '');
        $this->data['meta_description'] = array_fill_keys(ArticleLocale::SUPPORTED, '');
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
                    $this->data['published_at'] = static::resolvePublishNowDate($this->data['published_at'] ?? null);
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

    /**
     * The date "Publish Now" saves. Pulled out of the action closure so it
     * can be tested directly instead of through Filament's action-testing
     * machinery — a backdated date the author already picked (a legitimate
     * way to publish a historical post) is preserved; an empty field
     * defaults to right now.
     *
     * blank(), not ??=: Filament leaves an untouched date field as '' rather
     * than null.
     */
    public static function resolvePublishNowDate(?string $publishedAt): string
    {
        return blank($publishedAt) ? now()->format('Y-m-d H:i:s') : $publishedAt;
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
