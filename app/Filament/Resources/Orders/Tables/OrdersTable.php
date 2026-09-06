<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Models\Order;
use App\Support\OrderStatus;
use App\Support\PaymentStatus;
use App\Support\ProductType;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_number')
                    ->label('Nomor')
                    ->searchable()
                    ->copyable()
                    ->weight('bold')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Masuk')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                TextColumn::make('customer_name')
                    ->label('Pembeli')
                    ->searchable()
                    ->description(fn (Order $record) => $record->customer_phone),

                TextColumn::make('product_name_snapshot')
                    ->label('Produk')
                    ->searchable()
                    ->limit(35)
                    ->description(fn (Order $record) => ProductType::label($record->product_type_snapshot)),

                TextColumn::make('qty')
                    ->label('Qty')
                    ->alignCenter(),

                TextColumn::make('total')
                    ->label('Total')
                    ->formatStateUsing(fn (Order $record) => $record->formattedTotal())
                    // An unquoted shipping cost means this number is not final yet;
                    // saying so in the list avoids it being read as an invoice.
                    ->description(fn (Order $record) => $record->needsShipping() && $record->shipping_cost === null
                        ? 'ongkir belum dihitung'
                        : null)
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => OrderStatus::label($state))
                    ->color(fn (?string $state) => OrderStatus::color($state)),

                TextColumn::make('payment_status')
                    ->label('Pembayaran')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => PaymentStatus::label($state))
                    ->color(fn (?string $state) => PaymentStatus::color($state)),

                TextColumn::make('paid_at')
                    ->label('Dibayar')
                    ->dateTime('d M Y H:i')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status Pesanan')
                    ->options(OrderStatus::options()),

                SelectFilter::make('payment_status')
                    ->label('Status Pembayaran')
                    ->options(PaymentStatus::options()),
            ])
            ->recordActions([
                // One click for the move an order actually makes next, so the
                // common case never needs the full edit form.
                Action::make('process')
                    ->label('Proses')
                    ->icon('heroicon-o-play')
                    ->color('info')
                    ->visible(fn (Order $record) => $record->status === OrderStatus::BARU)
                    ->action(fn (Order $record) => static::moveTo($record, OrderStatus::DIPROSES)),

                Action::make('complete')
                    ->label('Selesai')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Tandai pesanan selesai?')
                    ->visible(fn (Order $record) => $record->status === OrderStatus::DIPROSES)
                    ->action(fn (Order $record) => static::moveTo($record, OrderStatus::SELESAI)),

                Action::make('cancel')
                    ->label('Batalkan')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Batalkan pesanan ini?')
                    ->modalDescription('Pesanan tetap tersimpan sebagai riwayat, hanya ditandai dibatalkan.')
                    ->visible(fn (Order $record) => ! in_array($record->status, [
                        OrderStatus::SELESAI,
                        OrderStatus::DIBATALKAN,
                    ], true))
                    ->action(fn (Order $record) => static::moveTo($record, OrderStatus::DIBATALKAN)),

                EditAction::make(),
            ])
            // No bulk delete: an order is a business record. Cancelling is a
            // status, and deleting one is a super_admin decision made one at a
            // time (see OrderPolicy).
            ->toolbarActions([])
            ->defaultSort('created_at', 'desc');
    }

    protected static function moveTo(Order $order, string $status): void
    {
        $order->update(['status' => $status]);

        Notification::make()
            ->title('Status pesanan ' . $order->order_number . ' → ' . OrderStatus::label($status))
            ->success()
            ->send();
    }
}
