<?php

namespace App\Filament\Resources\PaymentNotificationLogs;

use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Resources\PaymentNotificationLogs\Pages\ListPaymentNotificationLogs;
use App\Models\PaymentNotificationLog;
use App\Policies\OrderPolicy;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

/**
 * Midtrans Webhook Notification Monitor & Audit Log.
 *
 * Allows super_admin and admin to review all webhook events sent by Midtrans,
 * verify signatures, inspect JSON payloads, and troubleshoot any payment callback issues.
 */
class PaymentNotificationLogResource extends Resource
{
    protected static ?string $model = PaymentNotificationLog::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-bell-alert';

    protected static ?string $navigationLabel = 'Notifikasi Midtrans';

    protected static ?string $modelLabel = 'Log Notifikasi Webhook';

    protected static ?string $pluralModelLabel = 'Riwayat Notifikasi Midtrans';

    protected static string|\UnitEnum|null $navigationGroup = 'Katalog';

    protected static ?int $navigationSort = 26;

    public static function canAccess(): bool
    {
        return OrderPolicy::userIsManager(Auth::user());
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M Y H:i:s')
                    ->sortable()
                    ->description(fn (PaymentNotificationLog $record): string => $record->created_at?->diffForHumans() ?? '—'),

                TextColumn::make('order_id')
                    ->label('Order ID')
                    ->searchable()
                    ->copyable()
                    ->weight('bold')
                    ->placeholder('—'),

                TextColumn::make('payment_type')
                    ->label('Metode')
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(fn (?string $state): string => strtoupper($state ?? '—'))
                    ->placeholder('—'),

                TextColumn::make('transaction_status')
                    ->label('Status Midtrans')
                    ->badge()
                    ->color(fn (PaymentNotificationLog $record): string => $record->statusColor())
                    ->formatStateUsing(fn (?string $state): string => strtoupper($state ?? '—'))
                    ->placeholder('—'),

                TextColumn::make('gross_amount')
                    ->label('Nominal')
                    ->formatStateUsing(fn (PaymentNotificationLog $record): string => $record->formattedAmount())
                    ->sortable(),

                TextColumn::make('is_valid_signature')
                    ->label('Signature')
                    ->badge()
                    ->color(fn (bool $state): string => $state ? 'success' : 'danger')
                    ->formatStateUsing(fn (bool $state): string => $state ? '✓ Valid' : '✗ Invalid'),

                TextColumn::make('is_test_notification')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn (bool $state): string => $state ? 'warning' : 'gray')
                    ->formatStateUsing(fn (bool $state): string => $state ? '🧪 Test / Probe' : '🛒 Real Order'),

                TextColumn::make('response_status')
                    ->label('HTTP Respon')
                    ->badge()
                    ->color(fn (int $state): string => match ($state) {
                        200 => 'success',
                        404 => 'warning',
                        default => 'danger',
                    })
                    ->formatStateUsing(fn (int $state): string => (string) $state),

                TextColumn::make('ip_address')
                    ->label('IP Midtrans')
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->placeholder('—'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('transaction_status')
                    ->label('Status Transaksi')
                    ->options([
                        'settlement' => 'Settlement (Lunas)',
                        'capture'    => 'Capture (Kartu Kredit)',
                        'pending'    => 'Pending',
                        'deny'       => 'Deny (Ditolak)',
                        'cancel'     => 'Cancel (Dibatalkan)',
                        'expire'     => 'Expire (Kedaluwarsa)',
                    ]),

                SelectFilter::make('is_test_notification')
                    ->label('Tipe Event')
                    ->options([
                        '1' => '🧪 Simulasi / Test Probe',
                        '0' => '🛒 Real Order',
                    ]),

                SelectFilter::make('response_status')
                    ->label('HTTP Respon')
                    ->options([
                        200 => '200 OK',
                        403 => '403 Forbidden',
                        404 => '404 Not Found',
                    ]),
            ])
            ->recordActions([
                Action::make('view_details')
                    ->label('Lihat Payload')
                    ->icon('heroicon-o-code-bracket')
                    ->color('gray')
                    ->modalHeading(fn (PaymentNotificationLog $record): string => 'Payload Webhook: ' . ($record->order_id ?: 'Unknown'))
                    ->modalDescription(fn (PaymentNotificationLog $record): string => 'Diterima pada ' . ($record->created_at?->format('d M Y H:i:s') ?? '—') . ' dari IP ' . ($record->ip_address ?? '—'))
                    ->schema([
                        Placeholder::make('summary')
                            ->label('Ringkasan Respon')
                            ->content(fn (PaymentNotificationLog $record) => new HtmlString(
                                '<div style="margin-bottom: 1rem; padding: 0.75rem; border-radius: 0.5rem; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.1); font-size: 0.875rem;">' .
                                '<div><strong>Status HTTP Kami:</strong> <span style="font-family: monospace; font-weight: bold;">' . e($record->response_status) . ' (' . e($record->response_message ?? '-') . ')</span></div>' .
                                '<div><strong>Midtrans Transaction ID:</strong> <span style="font-family: monospace;">' . e($record->transaction_id ?? '-') . '</span></div>' .
                                '<div><strong>Fraud Status:</strong> <span style="font-family: monospace;">' . e($record->fraud_status ?? '-') . '</span></div>' .
                                '<div><strong>Status Code:</strong> <span style="font-family: monospace;">' . e($record->status_code ?? '-') . '</span></div>' .
                                '</div>'
                            )),

                        Placeholder::make('payload_json')
                            ->label('Raw JSON Body')
                            ->content(fn (PaymentNotificationLog $record) => new HtmlString(
                                '<pre style="background: #0f172a; color: #38bdf8; padding: 1rem; border-radius: 0.5rem; overflow-x: auto; font-size: 0.8125rem; line-height: 1.4; border: 1px solid rgba(56, 189, 248, 0.2); max-height: 400px;">' .
                                e(json_encode($record->payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) .
                                '</pre>'
                            )),
                    ]),

                Action::make('open_order')
                    ->label('Buka Pesanan')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->color('primary')
                    ->visible(fn (PaymentNotificationLog $record): bool => (bool) $record->order)
                    ->url(fn (PaymentNotificationLog $record): ?string => $record->order ? OrderResource::getUrl('edit', ['record' => $record->order]) : null)
                    ->openUrlInNewTab(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPaymentNotificationLogs::route('/'),
        ];
    }
}
