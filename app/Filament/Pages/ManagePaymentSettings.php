<?php

namespace App\Filament\Pages;

use App\Policies\OrderPolicy;
use App\Services\Payment\MidtransGateway;
use App\Settings\PaymentSettings;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;
use Midtrans\Config as MidtransConfig;
use Midtrans\Transaction;

/**
 * Control & Monitor Midtrans Core API and Manual Transfer settings.
 *
 * Accessible by super_admin and admin.
 */
class ManagePaymentSettings extends SettingsPage
{
    protected static string $settings = PaymentSettings::class;

    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-credit-card';
    protected static ?string $navigationLabel = 'Pengaturan Pembayaran';
    protected static ?string $title           = 'Pengaturan Pembayaran & Midtrans Core API';
    protected static string|\UnitEnum|null $navigationGroup  = 'Katalog';
    protected static ?int    $navigationSort  = 25;

    public static function canAccess(): bool
    {
        return OrderPolicy::userIsManager(Auth::user());
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('viewLogs')
                ->label('Riwayat Notifikasi Webhook')
                ->icon('heroicon-o-bell-alert')
                ->color('gray')
                ->url(fn (): string => \App\Filament\Resources\PaymentNotificationLogs\PaymentNotificationLogResource::getUrl('index')),

            Action::make('testConnection')
                ->label('Uji Koneksi API Midtrans')
                ->icon('heroicon-o-signal')
                ->color('info')
                ->action(function () {
                    $settings = app(PaymentSettings::class);
                    $serverKey = (string) ($settings->midtrans_server_key ?: config('services.midtrans.server_key'));
                    $isProduction = (bool) ($settings->midtrans_is_production ?? config('services.midtrans.is_production', false));

                    if (blank($serverKey)) {
                        Notification::make()
                            ->title('Server Key Kosong')
                            ->body('Harap isi Server Key Midtrans terlebih dahulu dan simpan perubahan.')
                            ->warning()
                            ->send();
                        return;
                    }

                    MidtransConfig::$serverKey    = $serverKey;
                    MidtransConfig::$isProduction = $isProduction;

                    try {
                        Transaction::status('rb-probe-' . now()->format('YmdHis'));

                        Notification::make()
                            ->title('✅ Koneksi Berhasil!')
                            ->body('Server Key diterima oleh Midtrans (' . ($isProduction ? 'Production' : 'Sandbox') . ').')
                            ->success()
                            ->send();
                    } catch (\Throwable $e) {
                        $code = (int) $e->getCode();
                        if ($code === 404) {
                            Notification::make()
                                ->title('✅ Koneksi Berhasil!')
                                ->body('Server Key valid dan terhubung dengan baik ke Midtrans (' . ($isProduction ? 'Production' : 'Sandbox') . ').')
                                ->success()
                                ->send();
                        } elseif ($code === 401) {
                            Notification::make()
                                ->title('❌ Server Key Ditolak (401)')
                                ->body('Kunci salah atau tertukar antara Sandbox dan Production.')
                                ->danger()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Gagal Menghubungi Midtrans')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }
                }),
        ];
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('PaymentSettings')
                ->columnSpanFull()
                ->contained(false)
                ->tabs([

                    // ══════════════════════════════════════════════════════════
                    // TAB 1 — 💳 Midtrans Core API
                    // ══════════════════════════════════════════════════════════
                    Tab::make('💳  Midtrans Core API')
                        ->schema([

                            Section::make('Status & Kredensial')
                                ->description('Kendalikan integrasi pembayaran langsung (Core API) tanpa redirect Snap.')
                                ->icon('heroicon-o-key')
                                ->schema([
                                    Toggle::make('midtrans_is_active')
                                        ->label('Aktifkan Gateway Midtrans')
                                        ->helperText('Master switch. Bila mati, sistem otomatis beralih ke Transfer Bank Manual.')
                                        ->columnSpanFull(),

                                    Toggle::make('midtrans_is_production')
                                        ->label('Mode Production (Live)')
                                        ->helperText('Matikan untuk Sandbox (Uji Coba), nyalakan jika sudah siap menerima uang asli.')
                                        ->columnSpanFull(),

                                    Select::make('active_gateway')
                                        ->label('Metode Pembayaran Utama')
                                        ->options([
                                            'midtrans'        => 'Midtrans Core API (Otomatis)',
                                            'manual_transfer' => 'Transfer Bank Manual (Konfirmasi Manual)',
                                        ])
                                        ->required()
                                        ->columnSpanFull(),

                                    TextInput::make('midtrans_server_key')
                                        ->label('Server Key')
                                        ->password()
                                        ->revealable()
                                        ->helperText('Kunci server rahasia dari Midtrans (Settings > Access Keys).')
                                        ->columnSpan(1),

                                    TextInput::make('midtrans_client_key')
                                        ->label('Client Key')
                                        ->helperText('Kunci publik klien dari Midtrans (Settings > Access Keys).')
                                        ->columnSpan(1),
                                ])
                                ->columns(2),

                            Section::make('Metode Pembayaran Core API yang Ditampilkan')
                                ->description('Pilih saluran pembayaran yang ingin Anda tawarkan ke pembeli pada website.')
                                ->icon('heroicon-o-check-badge')
                                ->schema([
                                    CheckboxList::make('midtrans_enabled_channels')
                                        ->label('Saluran Pembayaran Aktif')
                                        ->options([
                                            MidtransGateway::CHANNEL_QRIS         => '📱 QRIS (Semua E-Wallet & Mobile Banking Nasional)',
                                            MidtransGateway::CHANNEL_BCA_VA       => '🏦 BCA Virtual Account',
                                            MidtransGateway::CHANNEL_BNI_VA       => '🏦 BNI Virtual Account',
                                            MidtransGateway::CHANNEL_BRI_VA       => '🏦 BRI Virtual Account (BRIVA)',
                                            MidtransGateway::CHANNEL_MANDIRI_BILL => '🏦 Mandiri Bill Payment (E-Channel)',
                                            MidtransGateway::CHANNEL_PERMATA_VA   => '🏦 Permata Virtual Account',
                                            MidtransGateway::CHANNEL_CIMB_VA      => '🏦 CIMB Niaga Virtual Account',
                                            MidtransGateway::CHANNEL_GOPAY        => '📲 GoPay (Direct Deeplink & QR)',
                                            MidtransGateway::CHANNEL_SHOPEEPAY    => '📲 ShopeePay (Direct Deeplink & QR)',
                                            MidtransGateway::CHANNEL_INDOMARET    => '🏪 Indomaret / Ceriamart (Kasir Retail)',
                                            MidtransGateway::CHANNEL_ALFAMART     => '🏪 Alfamart / Alfamidi / Dan+Dan (Kasir Retail)',
                                        ])
                                        ->columns(2)
                                        ->columnSpanFull(),
                                ]),

                            Section::make('Panduan Webhook Notifikasi')
                                ->icon('heroicon-o-arrow-path')
                                ->schema([
                                    Placeholder::make('webhook_url')
                                        ->label('Payment Notification URL')
                                        ->content(fn () => new HtmlString(
                                            '<div style="background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.1); padding:1rem; border-radius:0.75rem;">' .
                                            '<p style="margin:0 0 0.5rem 0; font-size:0.875rem;">Daftarkan URL berikut pada menu <strong>Settings &gt; Configuration &gt; Payment Notification URL</strong> di dashboard Midtrans (baik Sandbox maupun Production) agar status pembayaran pesanan otomatis terverifikasi LUNAS saat pembeli selesai membayar:</p>' .
                                            '<input type="text" readonly value="' . e(route('payment.midtrans.notification')) . '" style="width:100%; background:rgba(0,0,0,0.3); border:1px solid rgba(255,255,255,0.15); padding:0.5rem 0.75rem; border-radius:0.5rem; color:#38BDF8; font-family:monospace; font-weight:bold; margin-bottom:0.75rem;" onclick="this.select()">' .
                                            '<p style="margin:0; font-size:0.8125rem; color:rgba(255,255,255,0.7);">💡 <em>Tips:</em> Saat Anda menekan tombol "Simpan" atau "Test" di dashboard Midtrans, Midtrans akan mengirim sinyal simulasi (<code>payment_notif_test_...</code>). Sistem kami otomatis menerima dan mencatatnya ke <a href="' . e(\App\Filament\Resources\PaymentNotificationLogs\PaymentNotificationLogResource::getUrl('index')) . '" style="color:#38BDF8; text-decoration:underline;">Riwayat Notifikasi Midtrans</a> dengan respon HTTP 200 OK.</p>' .
                                            '</div>'
                                        ))
                                        ->columnSpanFull(),
                                ]),

                        ]),

                    // ══════════════════════════════════════════════════════════
                    // TAB 2 — 🏦 Transfer Bank Manual
                    // ══════════════════════════════════════════════════════════
                    Tab::make('🏦  Transfer Bank Manual')
                        ->schema([

                            Section::make('Rekening Bank Toko')
                                ->description('Ditampilkan ke pembeli jika metode Transfer Manual aktif atau saat Midtrans dimatikan.')
                                ->icon('heroicon-o-building-library')
                                ->schema([
                                    TextInput::make('manual_bank_name')
                                        ->label('Nama Bank')
                                        ->placeholder('BCA / Mandiri / BNI')
                                        ->maxLength(60),

                                    TextInput::make('manual_account_number')
                                        ->label('Nomor Rekening')
                                        ->placeholder('1234567890')
                                        ->maxLength(60),

                                    TextInput::make('manual_account_holder')
                                        ->label('Atas Nama')
                                        ->placeholder('PT / Nama Pemilik')
                                        ->maxLength(100),
                                ])
                                ->columns(3),

                        ]),

                ]),
        ]);
    }
}
