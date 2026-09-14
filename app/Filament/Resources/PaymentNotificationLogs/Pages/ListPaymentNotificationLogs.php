<?php

namespace App\Filament\Resources\PaymentNotificationLogs\Pages;

use App\Filament\Resources\PaymentNotificationLogs\PaymentNotificationLogResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListPaymentNotificationLogs extends ListRecords
{
    protected static string $resource = PaymentNotificationLogResource::class;

    public function getTabs(): array
    {
        return [
            'semua' => Tab::make('Semua Event'),

            'lunas' => Tab::make('Settlement / Lunas')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('transaction_status', 'settlement'))
                ->badge(fn () => static::countWhere('transaction_status', 'settlement')),

            'test' => Tab::make('🧪 Simulasi / Test')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_test_notification', true))
                ->badge(fn () => static::countWhere('is_test_notification', true)),

            'gagal_error' => Tab::make('⚠️ Error / Ditolak')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('response_status', '!=', 200))
                ->badge(fn () => static::getResource()::getEloquentQuery()->where('response_status', '!=', 200)->count() ?: null),
        ];
    }

    protected static function countWhere(string $column, mixed $value): ?int
    {
        $count = static::getResource()::getEloquentQuery()->where($column, $value)->count();

        return $count > 0 ? $count : null;
    }
}
