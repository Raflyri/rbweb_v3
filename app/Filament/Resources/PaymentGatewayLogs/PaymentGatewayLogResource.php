<?php

namespace App\Filament\Resources\PaymentGatewayLogs;

use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Resources\PaymentGatewayLogs\Pages\ListPaymentGatewayLogs;
use App\Models\PaymentGatewayLog;
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
use UnitEnum;

class PaymentGatewayLogResource extends Resource
{
    protected static ?string $model = PaymentGatewayLog::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-magnifying-glass';

    protected static ?string $navigationLabel = 'Log Payment Gateway';

    protected static ?string $modelLabel = 'Log Gateway';

    protected static ?string $pluralModelLabel = 'Log Payload Payment Gateway';

    protected static string|UnitEnum|null $navigationGroup = 'Katalog';

    protected static ?int $navigationSort = 27;

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
                    ->description(fn (PaymentGatewayLog $record): string => $record->created_at?->diffForHumans() ?? '—'),

                TextColumn::make('direction')
                    ->label('Arah')
                    ->badge()
                    ->color(fn (PaymentGatewayLog $record): string => $record->directionColor())
                    ->icon(fn (PaymentGatewayLog $record): string => $record->directionIcon())
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'incoming' => 'Masuk (Webhook)',
                        'outgoing' => 'Keluar (API)',
                        default    => ucfirst($state ?? '—'),
                    }),

                TextColumn::make('gateway')
                    ->label('Gateway')
                    ->badge()
                    ->color(fn (?string $state): string => match (strtolower($state ?? '')) {
                        'paylabs'  => 'warning',
                        'midtrans' => 'info',
                        default    => 'gray',
                    })
                    ->formatStateUsing(fn (?string $state): string => strtoupper($state ?? '—')),

                TextColumn::make('action')
                    ->label('Aksi')
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn (PaymentGatewayLog $record): string => $record->actionLabel()),

                TextColumn::make('order_id')
                    ->label('No. Pesanan')
                    ->searchable()
                    ->copyable()
                    ->weight('bold')
                    ->placeholder('—'),

                TextColumn::make('transaction_id')
                    ->label('Ref / Platform ID')
                    ->searchable()
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->placeholder('—'),

                TextColumn::make('amount')
                    ->label('Nominal')
                    ->formatStateUsing(fn (PaymentGatewayLog $record): string => $record->formattedAmount())
                    ->sortable(),

                TextColumn::make('response_status')
                    ->label('HTTP')
                    ->badge()
                    ->color(fn (?int $state): string => match ($state) {
                        200, 201 => 'success',
                        400, 401, 403, 404 => 'warning',
                        default  => 'danger',
                    })
                    ->formatStateUsing(fn (?int $state): string => (string) ($state ?? '—')),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (PaymentGatewayLog $record): string => $record->statusColor())
                    ->formatStateUsing(fn (?string $state): string => strtoupper($state ?? '—')),

                TextColumn::make('error_message')
                    ->label('Keterangan / Error')
                    ->limit(40)
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('gateway')
                    ->label('Gateway')
                    ->options([
                        'paylabs'  => 'Paylabs',
                        'midtrans' => 'Midtrans',
                    ]),

                SelectFilter::make('direction')
                    ->label('Arah Payload')
                    ->options([
                        'outgoing' => '↗ Keluar (RBeverything ➔ Gateway)',
                        'incoming' => '↙ Masuk (Gateway ➔ RBeverything Webhook)',
                    ]),

                SelectFilter::make('action')
                    ->label('Jenis Aksi')
                    ->options([
                        'create_qris'          => 'Generate QRIS',
                        'create_va'            => 'Generate Virtual Account',
                        'query_status'         => 'Cek Status Transaksi',
                        'cancel_qris'          => 'Batalkan QRIS',
                        'webhook_notification' => 'Webhook Notifikasi',
                    ]),

                SelectFilter::make('status')
                    ->label('Status Hasil')
                    ->options([
                        'success'           => 'Success (Berhasil)',
                        'failed'            => 'Failed (Gagal / Ditolak)',
                        'invalid_signature' => 'Invalid Signature',
                    ]),
            ])
            ->recordActions([
                Action::make('view_evidence')
                    ->label('Lihat Payload (Evidence)')
                    ->icon('heroicon-o-code-bracket')
                    ->color('primary')
                    ->modalHeading(fn (PaymentGatewayLog $record): string => 'Evidence Payload Gateway: ' . ($record->order_id ?: ('Log #' . $record->id)))
                    ->modalDescription(fn (PaymentGatewayLog $record): string => strtoupper($record->gateway) . ' • ' . $record->actionLabel() . ' • ' . ($record->created_at?->format('d M Y H:i:s') ?? '—'))
                    ->schema([
                        Placeholder::make('summary')
                            ->label('Ringkasan Transaksi')
                            ->content(fn (PaymentGatewayLog $record) => new HtmlString(
                                '<div style="margin-bottom: 1rem; padding: 0.85rem; border-radius: 0.5rem; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.1); font-size: 0.875rem; display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 0.5rem;">' .
                                '<div><strong>Gateway:</strong> <span style="font-family:monospace; text-transform:uppercase;">' . e($record->gateway) . '</span></div>' .
                                '<div><strong>Arah:</strong> <span>' . e($record->directionLabel()) . '</span></div>' .
                                '<div><strong>Aksi:</strong> <span>' . e($record->actionLabel()) . '</span></div>' .
                                '<div><strong>Order ID:</strong> <span style="font-family:monospace; font-weight:bold; color:#38BDF8;">' . e($record->order_id ?? '-') . '</span></div>' .
                                '<div><strong>Platform Trade No:</strong> <span style="font-family:monospace;">' . e($record->transaction_id ?? '-') . '</span></div>' .
                                '<div><strong>HTTP Code:</strong> <span style="font-family:monospace; font-weight:bold;">' . e($record->response_status ?? '-') . '</span></div>' .
                                '<div><strong>Status Hasil:</strong> <span style="font-family:monospace; font-weight:bold; color:' . ($record->status === 'success' ? '#34D399' : '#EF4444') . ';">' . e(strtoupper($record->status ?? '-')) . '</span></div>' .
                                ($record->error_message ? '<div style="grid-column: 1 / -1; color:#F87171;"><strong>Error:</strong> ' . e($record->error_message) . '</div>' : '') .
                                ($record->endpoint ? '<div style="grid-column: 1 / -1; font-family:monospace; font-size:0.75rem; color:#94A3B8;"><strong>Endpoint:</strong> ' . e($record->method . ' ' . $record->endpoint) . '</div>' : '') .
                                '</div>'
                            )),

                        Placeholder::make('request_payload_json')
                            ->label('Request Payload (Yang Dikirim)')
                            ->content(fn (PaymentGatewayLog $record) => new HtmlString(
                                '<div style="position:relative;">' .
                                '<div style="font-size:0.75rem; color:#94A3B8; margin-bottom:0.25rem;">Headers: ' . e(json_encode($record->request_headers, JSON_UNESCAPED_SLASHES)) . '</div>' .
                                '<pre style="background: #0f172a; color: #38bdf8; padding: 1rem; border-radius: 0.5rem; overflow-x: auto; font-size: 0.8125rem; line-height: 1.4; border: 1px solid rgba(56, 189, 248, 0.2); max-height: 300px; user-select:all;">' .
                                e(json_encode($record->request_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: '{}') .
                                '</pre>' .
                                '</div>'
                            )),

                        Placeholder::make('response_payload_json')
                            ->label('Response Payload (Yang Diterima)')
                            ->content(fn (PaymentGatewayLog $record) => new HtmlString(
                                '<div style="position:relative;">' .
                                '<pre style="background: #0f172a; color: #a7f3d0; padding: 1rem; border-radius: 0.5rem; overflow-x: auto; font-size: 0.8125rem; line-height: 1.4; border: 1px solid rgba(167, 243, 208, 0.2); max-height: 300px; user-select:all;">' .
                                e(json_encode($record->response_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: '{}') .
                                '</pre>' .
                                '</div>'
                            )),

                        Placeholder::make('evidence_all_json')
                            ->label('Evidence Siap Kirim (Request + Response)')
                            ->content(fn (PaymentGatewayLog $record) => new HtmlString(
                                '<div style="margin-top:0.5rem;">' .
                                '<p style="font-size:0.75rem; color:#94A3B8; margin-bottom:0.25rem;">Klik di dalam kotak di bawah untuk memilih semua teks (evidence lengkap) untuk dikirimkan ke pihak gateway:</p>' .
                                '<textarea readonly style="width:100%; height:160px; background:#18181b; color:#fbbf24; font-family:monospace; font-size:0.75rem; border:1px solid rgba(251,191,36,0.3); border-radius:0.5rem; padding:0.75rem;" onclick="this.select()">' .
                                e($record->generateEvidenceText()) .
                                '</textarea>' .
                                '</div>'
                            )),
                    ]),

                Action::make('open_order')
                    ->label('Buka Pesanan')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->color('primary')
                    ->visible(fn (PaymentGatewayLog $record): bool => (bool) $record->order)
                    ->url(fn (PaymentGatewayLog $record): ?string => $record->order ? OrderResource::getUrl('edit', ['record' => $record->order]) : null)
                    ->openUrlInNewTab(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPaymentGatewayLogs::route('/'),
        ];
    }
}
