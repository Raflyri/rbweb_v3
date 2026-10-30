<?php

namespace App\Filament\Resources\PaymentGatewayLogs\Pages;

use App\Filament\Resources\PaymentGatewayLogs\PaymentGatewayLogResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListPaymentGatewayLogs extends ListRecords
{
    protected static string $resource = PaymentGatewayLogResource::class;

    public function getTabs(): array
    {
        return [
            'semua' => Tab::make('Semua Log'),

            'paylabs' => Tab::make('Paylabs')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('gateway', 'paylabs'))
                ->badge(fn () => static::countWhere('gateway', 'paylabs')),

            'outgoing' => Tab::make('↗ Keluar (API Request)')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('direction', 'outgoing'))
                ->badge(fn () => static::countWhere('direction', 'outgoing')),

            'incoming' => Tab::make('↙ Masuk (Webhook)')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('direction', 'incoming'))
                ->badge(fn () => static::countWhere('direction', 'incoming')),

            'gagal' => Tab::make('⚠️ Gagal / Ditolak')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', '!=', 'success'))
                ->badge(fn () => static::getResource()::getEloquentQuery()->where('status', '!=', 'success')->count() ?: null),
        ];
    }

    protected static function countWhere(string $column, mixed $value): ?int
    {
        $count = static::getResource()::getEloquentQuery()->where($column, $value)->count();

        return $count > 0 ? $count : null;
    }
}
