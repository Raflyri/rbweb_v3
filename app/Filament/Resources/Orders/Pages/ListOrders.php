<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use App\Support\OrderStatus;
use App\Support\PaymentStatus;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    /**
     * The three questions actually asked of this screen: what is new, what is
     * still owed, and what is done.
     */
    public function getTabs(): array
    {
        return [
            'baru' => Tab::make('Baru')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', OrderStatus::BARU))
                ->badge(fn () => static::countWhere('status', OrderStatus::BARU)),

            'berjalan' => Tab::make('Berjalan')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', OrderStatus::DIPROSES)),

            'belum_bayar' => Tab::make('Belum Lunas')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('payment_status', [
                    PaymentStatus::MENUNGGU,
                    PaymentStatus::MENUNGGU_VERIFIKASI,
                ])->where('status', '!=', OrderStatus::DIBATALKAN)),

            'semua' => Tab::make('Semua'),
        ];
    }

    protected static function countWhere(string $column, string $value): ?int
    {
        $count = static::getResource()::getEloquentQuery()->where($column, $value)->count();

        return $count > 0 ? $count : null;
    }
}
