<?php

namespace App\Filament\Resources\CustomPageResource\Pages;

use App\Filament\Resources\CustomPageResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCustomPage extends EditRecord
{
    protected static string $resource = CustomPageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('visit')
                ->label('Buka Halaman')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->color('gray')
                ->url(fn (): string => url($this->record->path))
                ->openUrlInNewTab(),
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
