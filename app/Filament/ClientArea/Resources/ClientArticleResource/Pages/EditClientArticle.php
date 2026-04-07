<?php

namespace App\Filament\ClientArea\Resources\ClientArticleResource\Pages;

use App\Filament\ClientArea\Resources\ClientArticleResource;
use App\Models\Article;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditClientArticle extends EditRecord
{
    protected static string $resource = ClientArticleResource::class;

    // No custom $view in Filament v4 — Schema handles form+actions natively.
    public function getExtraAttributes(): array
    {
        return ['class' => 'article-editor-page'];
    }

    // Show a soft reminder but do NOT lock out editing of published articles
    public function mount(int|string $record): void
    {
        parent::mount($record);

        /** @var Article $article */
        $article = $this->record;

        if ($article->isPublished()) {
            \Filament\Notifications\Notification::make()
                ->title('Artikel sudah Published')
                ->body('Perubahan pada artikel ini akan langsung diterapkan ke halaman publik.')
                ->warning()
                ->send();
        }
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Spatie Translatable stores translatable fields as JSON arrays keyed by locale.
        // The form uses dot-notation fields (title.id, title.en, content.id, …) which
        // Filament resolves from the nested array automatically.
        // Do NOT flatten the arrays here — doing so wipes every other locale on save.
        return $data;
    }


    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
