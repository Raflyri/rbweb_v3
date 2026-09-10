<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // The buyer's own copy of this order — handy when they ask "which
            // link did I get?" over WhatsApp.
            Action::make('view_public')
                ->label('Halaman Pembeli')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->color('gray')
                ->url(fn (): string => route('order.pending', $this->record->public_token))
                ->openUrlInNewTab(),

            // Only super_admin, and only ever one at a time (see OrderPolicy):
            // deleting an order destroys the record of what was agreed.
            DeleteAction::make(),
        ];
    }
}
