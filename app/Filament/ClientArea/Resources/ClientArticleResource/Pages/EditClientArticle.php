<?php

namespace App\Filament\ClientArea\Resources\ClientArticleResource\Pages;

use App\Filament\ClientArea\Resources\ClientArticleResource;
use App\Models\Article;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditClientArticle extends EditRecord
{
    protected static string $resource = ClientArticleResource::class;

    // Shared premium layout with CreateClientArticle
    protected string $view = 'filament.client-area.articles.article-editor';

    // Prevent editing published articles
    public function mount(int|string $record): void
    {
        parent::mount($record);

        /** @var Article $article */
        $article = $this->record;

        if ($article->isPublished()) {
            \Filament\Notifications\Notification::make()
                ->title('Published articles cannot be edited.')
                ->info()
                ->send();

            $this->redirect(ClientArticleResource::getUrl('index'));
        }
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Spatie Translatable casts return arrays in toArray(). 
        // We unpack them for the Filament form fields.
        $locale = app()->getLocale();
        foreach (['title', 'content', 'meta_description'] as $field) {
            if (isset($data[$field]) && is_array($data[$field])) {
                $data[$field] = $data[$field][$locale] 
                    ?? $data[$field]['id'] 
                    ?? $data[$field]['en'] 
                    ?? (array_values($data[$field])[0] ?? null);
            }
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
