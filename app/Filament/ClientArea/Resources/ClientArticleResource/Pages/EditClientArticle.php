<?php

namespace App\Filament\ClientArea\Resources\ClientArticleResource\Pages;

use App\Filament\ClientArea\Resources\ClientArticleResource;
use App\Models\Article;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditClientArticle extends EditRecord
{
    protected static string $resource = ClientArticleResource::class;

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

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
