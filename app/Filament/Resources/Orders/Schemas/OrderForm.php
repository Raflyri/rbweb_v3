<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Support\OrderStatus;
use App\Support\PaymentStatus;
use App\Support\ProductType;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

/**
 * The order edit screen.
 *
 * Split deliberately into what the buyer said (read-only) and what the shop
 * decides (editable). Nothing a customer submitted can be quietly rewritten
 * here: if a phone number was mistyped, that belongs in the notes, not in an
 * edit that erases what was actually received.
 */
class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([

                // ── What was ordered (frozen) ────────────────────────────
                Section::make('Pesanan')
                    ->description('Disalin saat pesanan dibuat — tidak ikut berubah kalau produknya diedit belakangan.')
                    ->columnSpan(1)
                    ->schema([
                        TextInput::make('order_number')
                            ->label('Nomor Pesanan')
                            ->disabled(),

                        TextInput::make('product_name_snapshot')
                            ->label('Produk')
                            ->disabled(),

                        TextInput::make('product_type_snapshot')
                            ->label('Jenis')
                            ->formatStateUsing(fn (?string $state) => ProductType::label($state))
                            ->disabled(),

                        TextInput::make('qty')
                            ->label('Jumlah')
                            ->disabled(),

                        TextInput::make('price_snapshot')
                            ->label('Harga Satuan')
                            ->prefix('Rp')
                            ->disabled(),

                        TextInput::make('subtotal')
                            ->label('Subtotal')
                            ->prefix('Rp')
                            ->disabled(),
                    ]),

                // ── What the buyer told us (frozen) ─────────────────────
                Section::make('Pembeli')
                    ->columnSpan(1)
                    ->schema([
                        TextInput::make('customer_name')->label('Nama')->disabled(),
                        TextInput::make('customer_email')->label('Email')->disabled(),
                        TextInput::make('customer_phone')->label('WhatsApp/HP')->disabled(),

                        Textarea::make('shipping_address')
                            ->label('Alamat Pengiriman')
                            ->rows(3)
                            ->disabled()
                            ->visible(fn (Get $get): bool => filled($get('shipping_address'))),

                        DatePicker::make('preferred_date')
                            ->label('Tanggal Diinginkan')
                            ->displayFormat('d/m/Y')
                            ->disabled()
                            ->visible(fn (Get $get): bool => filled($get('preferred_date'))),

                        Textarea::make('notes')
                            ->label('Catatan Pembeli')
                            ->rows(3)
                            ->disabled()
                            ->visible(fn (Get $get): bool => filled($get('notes'))),
                    ]),

                // ── What we decide ──────────────────────────────────────
                Section::make('Penanganan')
                    ->columnSpan(1)
                    ->schema([
                        Select::make('status')
                            ->label('Status Pesanan')
                            ->options(OrderStatus::options())
                            ->default(OrderStatus::DEFAULT)
                            ->required()
                            ->native(false),

                        TextInput::make('shipping_cost')
                            ->label('Ongkos Kirim')
                            ->numeric()
                            ->minValue(0)
                            ->prefix('Rp')
                            ->live(debounce: 500)
                            // Keeping the total in step with the shipping cost by
                            // hand is exactly the sort of arithmetic that ends up
                            // wrong on an invoice.
                            ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                $set('total', (float) $get('subtotal') + (float) ($state ?: 0));
                            })
                            ->helperText('Isi setelah ongkir dikonfirmasi ke pembeli. Total ikut terhitung otomatis.'),

                        TextInput::make('total')
                            ->label('Total Tagihan')
                            ->numeric()
                            ->minValue(0)
                            ->prefix('Rp')
                            ->helperText('Boleh ditimpa manual kalau ada diskon atau kesepakatan lain.'),
                    ]),

                // ── Money ───────────────────────────────────────────────
                Section::make('Pembayaran')
                    ->columnSpan(1)
                    ->schema([
                        Select::make('payment_status')
                            ->label('Status Pembayaran')
                            ->options(PaymentStatus::options())
                            ->default(PaymentStatus::DEFAULT)
                            ->required()
                            ->native(false)
                            ->live()
                            // Marking an order paid without recording when is how
                            // a bookkeeping question becomes unanswerable later.
                            ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                if ($state === PaymentStatus::LUNAS && blank($get('paid_at'))) {
                                    $set('paid_at', now());
                                }
                            }),

                        TextInput::make('payment_method')
                            ->label('Metode Pembayaran')
                            ->maxLength(40)
                            ->placeholder('transfer bank / tunai')
                            ->helperText('Diisi otomatis mulai fase pembayaran; sementara ini boleh manual.'),

                        DateTimePicker::make('paid_at')
                            ->label('Waktu Pembayaran')
                            ->native(false)
                            ->displayFormat('d/m/Y H:i'),

                        Textarea::make('payment_note')
                            ->label('Catatan Pembayaran')
                            ->rows(3)
                            ->maxLength(1000)
                            ->helperText('Mis. alasan penolakan bukti transfer, atau nomor referensi.'),
                    ]),

            ]);
    }
}
