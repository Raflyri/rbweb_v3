<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Straight to the live page, so checking how an edit actually
            // looks does not mean hunting for the URL by hand.
            Action::make('view_public')
                ->label('Lihat Halaman Publik')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->color('gray')
                // The public catalogue arrives in the next phase; until its
                // route is registered the button simply stays hidden rather
                // than blowing up the whole edit page.
                ->visible(fn (): bool => \Illuminate\Support\Facades\Route::has('products.show'))
                ->url(fn (): string => route('products.show', $this->record->slug))
                ->openUrlInNewTab(),

            DeleteAction::make(),
        ];
    }
}
