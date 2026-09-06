<?php

namespace App\Filament\Resources\Orders;

use App\Filament\Resources\Orders\Pages\EditOrder;
use App\Filament\Resources\Orders\Pages\ListOrders;
use App\Filament\Resources\Orders\Schemas\OrderForm;
use App\Filament\Resources\Orders\Tables\OrdersTable;
use App\Models\Order;
use App\Policies\OrderPolicy;
use App\Support\OrderStatus;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

/**
 * Order management — admin/super_admin only.
 *
 * Like ProductResource, this lives under app/Filament/Resources, which only the
 * admin panel scans, so it can never surface in the Client Area. Orders hold
 * customers' names, phone numbers and home addresses; that is reason enough to
 * keep the whole resource behind the same two roles.
 */
class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'Pesanan';

    protected static ?string $modelLabel = 'Pesanan';

    protected static ?string $pluralModelLabel = 'Pesanan';

    protected static string|\UnitEnum|null $navigationGroup = 'Katalog';

    protected static ?int $navigationSort = 20;

    protected static ?string $recordTitleAttribute = 'order_number';

    public static function canViewAny(): bool
    {
        return OrderPolicy::userIsManager(auth()->user());
    }

    /** Orders arrive from the public form; there is nothing to create here. */
    public static function canCreate(): bool
    {
        return false;
    }

    /** How many need attention right now — the reason to open this at all. */
    public static function getNavigationBadge(): ?string
    {
        $count = Order::query()->where('status', OrderStatus::BARU)->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Schema $schema): Schema
    {
        return OrderForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OrdersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOrders::route('/'),
            'edit'  => EditOrder::route('/{record}/edit'),
        ];
    }
}
