<?php

namespace App\Filament\Pages;

use App\Policies\OrderPolicy;
use App\Services\Payment\MidtransGateway;
use App\Services\Payment\PaylabsGateway;
use App\Settings\PaymentSettings;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\HtmlString;
use Midtrans\Config as MidtransConfig;
use Midtrans\Transaction;

/**
 * Control & Monitor Midtrans Core API, Paylabs Payment Gateway, and Manual Transfer settings.
 */
class ManagePaymentSettings extends SettingsPage
{
    protected static string $settings = PaymentSettings::class;

    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-credit-card';
    protected static ?string $navigationLabel = 'Pengaturan Pembayaran';
    protected static ?string $title           = 'Pengaturan Pembayaran (Midtrans & Paylabs)';
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

            Action::make('testMidtrans')
                ->label('Uji Koneksi Midtrans')
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
                            ->title('✅ Koneksi Midtrans Berhasil!')
                            ->body('Server Key diterima oleh Midtrans (' . ($isProduction ? 'Production' : 'Sandbox') . ').')
                            ->success()
                            ->send();
                    } catch (\Throwable $e) {
                        $code = (int) $e->getCode();
                        if ($code === 404) {
                            Notification::make()
                                ->title('✅ Koneksi Midtrans Berhasil!')
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

            Action::make('testPaylabs')
                ->label('Uji Koneksi Paylabs')
                ->icon('heroicon-o-bolt')
                ->color('warning')
                ->action(function () {
                    $settings   = app(PaymentSettings::class);
                    $merchantId = (string) ($settings->paylabs_merchant_id ?: config('services.paylabs.merchant_id'));
                    $privKey    = (string) ($settings->paylabs_private_key ?: config('services.paylabs.private_key'));
                    $isProd     = (bool) ($settings->paylabs_is_production ?? config('services.paylabs.is_production', false));

                    if (blank($merchantId) || blank($privKey)) {
                        Notification::make()
                            ->title('Kredensial Paylabs Belum Lengkap')
                            ->body('Harap lengkapi Merchant ID dan RSA Private Key Paylabs terlebih dahulu.')
                            ->warning()
                            ->send();
                        return;
                    }

                    $paylabs = new PaylabsGateway();
                    $baseUrl = $isProd ? 'https://pay.paylabs.co.id' : 'https://sit-pay.paylabs.co.id';
                    $timestamp = (new \DateTime('now', new \DateTimeZone('Asia/Jakarta')))->format('Y-m-d\TH:i:s.vP');
                    $body = [
                        'merchantId'      => $merchantId,
                        'requestId'       => 'TEST-CONN-' . time(),
                        'merchantTradeNo' => 'PROBE-CONN-' . time(),
                    ];

                    try {
                        $minified = json_encode($body, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                        $sig = $paylabs->generateSignature('POST', '/payment/v2.3/qris/query', $body, $timestamp, $privKey);
                        $res = Http::withHeaders([
                            'Content-Type' => 'application/json;charset=utf-8',
                            'X-TIMESTAMP'  => $timestamp,
                            'X-PARTNER-ID' => $merchantId,
                            'X-REQUEST-ID' => $body['requestId'],
                            'X-SIGNATURE'  => $sig,
                        ])->withBody($minified, 'application/json;charset=utf-8')->timeout(8)->post($baseUrl . '/payment/v2.3/qris/query');

                        $json = $res->json();
                        // If Paylabs responds with valid JSON (even if order not found, signature & auth passed)
                        if ($res->status() === 200 || ($json && in_array((string)($json['errCode'] ?? ''), ['0', '01', '02', '10001', '10002'], true))) {
                            Notification::make()
                                ->title('✅ Koneksi Paylabs Berhasil!')
                                ->body('Kredensial diterima oleh Paylabs Gateway (' . ($isProd ? 'Production' : 'Sandbox') . ').')
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Respon Paylabs: HTTP ' . $res->status())
                                ->body($json['errCodeDes'] ?? ($res->body() ?: 'Gagal autentikasi'))
                                ->warning()
                                ->send();
                        }
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('❌ Gagal Menghubungi Paylabs')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('⚙️ Mode Operasional Gateway Pembayaran')
                ->description('Tentukan apakah sistem menggunakan satu jenis gateway saja (Midtrans/Paylabs), mengaktifkan keduanya sekaligus, atau hanya transfer bank manual.')
                ->schema([
                    Select::make('active_gateway')
                        ->label('Mode Gateway Aktif')
                        ->options([
                            'both'            => '⚡ Keduanya Aktif (Midtrans & Paylabs - Pelanggan dapat memilih saluran dari kedua gateway)',
                            'midtrans'        => '💳 Hanya Midtrans Core API',
                            'paylabs'         => '🚀 Hanya Paylabs Payment Gateway',
                            'manual_transfer' => '🏦 Hanya Transfer Bank Manual',
                        ])
                        ->helperText('Jika memilih "Keduanya Aktif", semua saluran pembayaran aktif dari Midtrans dan Paylabs akan ditampilkan ke pelanggan.')
                        ->required()
                        ->columnSpanFull(),
                ]),

            Tabs::make('PaymentSettings')
                ->columnSpanFull()
                ->contained(false)
                ->tabs([

                    // ══════════════════════════════════════════════════════════
                    // TAB 1 — 💳 Midtrans Core API
                    // ══════════════════════════════════════════════════════════
                    Tab::make('💳  Midtrans Core API')
                        ->schema([

                            Section::make('Status & Kredensial Midtrans')
                                ->description('Kendalikan integrasi pembayaran langsung (Core API) tanpa redirect Snap.')
                                ->icon('heroicon-o-key')
                                ->schema([
                                    Toggle::make('midtrans_is_active')
                                        ->label('Aktifkan Gateway Midtrans')
                                        ->helperText('Bila mati, saluran pembayaran Midtrans tidak akan ditawarkan.')
                                        ->columnSpan(1),

                                    Toggle::make('midtrans_is_production')
                                        ->label('Mode Production Midtrans (Live)')
                                        ->helperText('Matikan untuk Sandbox (api.sandbox.midtrans.com), nyalakan untuk Production (api.midtrans.com).')
                                        ->columnSpan(1),

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

                            Section::make('Saluran Pembayaran Midtrans yang Ditampilkan')
                                ->description('Pilih saluran pembayaran Midtrans yang ingin ditawarkan ke pembeli.')
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

                            Section::make('Panduan Webhook Notifikasi Midtrans')
                                ->icon('heroicon-o-arrow-path')
                                ->schema([
                                    Placeholder::make('webhook_url')
                                        ->label('Payment Notification URL Midtrans')
                                        ->content(fn () => new HtmlString(
                                            '<div style="background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.1); padding:1rem; border-radius:0.75rem;">' .
                                            '<p style="margin:0 0 0.5rem 0; font-size:0.875rem;">Daftarkan URL berikut pada menu <strong>Settings &gt; Configuration &gt; Payment Notification URL</strong> di dashboard Midtrans:</p>' .
                                            '<input type="text" readonly value="' . e(route('payment.midtrans.notification')) . '" style="width:100%; background:rgba(0,0,0,0.3); border:1px solid rgba(255,255,255,0.15); padding:0.5rem 0.75rem; border-radius:0.5rem; color:#38BDF8; font-family:monospace; font-weight:bold; margin-bottom:0.75rem;" onclick="this.select()">' .
                                            '<p style="margin:0; font-size:0.8125rem; color:rgba(255,255,255,0.7);">💡 Sistem otomatis menerima sinyal simulasi (<code>payment_notif_test_...</code>) dan mencatatnya ke log webhook.</p>' .
                                            '</div>'
                                        ))
                                        ->columnSpanFull(),
                                ]),

                        ]),

                    // ══════════════════════════════════════════════════════════
                    // TAB 2 — 🚀 Paylabs Payment Gateway
                    // ══════════════════════════════════════════════════════════
                    Tab::make('🚀  Paylabs Gateway')
                        ->schema([

                            Section::make('Status & Kredensial Paylabs')
                                ->description('Konfigurasi kredensial Paylabs API v2.3 (Merchant ID, Store ID, RSA Keys).')
                                ->icon('heroicon-o-shield-check')
                                ->schema([
                                    Toggle::make('paylabs_is_active')
                                        ->label('Aktifkan Gateway Paylabs')
                                        ->helperText('Bila mati, saluran pembayaran Paylabs tidak akan ditawarkan.')
                                        ->columnSpan(1),

                                    Toggle::make('paylabs_is_production')
                                        ->label('Mode Production Paylabs (Live)')
                                        ->helperText('Matikan untuk Sandbox (sit-pay.paylabs.co.id), nyalakan untuk Production (pay.paylabs.co.id).')
                                        ->columnSpan(1),

                                    TextInput::make('paylabs_merchant_id')
                                        ->label('Merchant ID / Partner ID')
                                        ->placeholder('Contoh: 0010001')
                                        ->helperText('Partner ID yang diberikan oleh pihak Paylabs.')
                                        ->columnSpan(1),

                                    TextInput::make('paylabs_store_id')
                                        ->label('Store ID (Opsional)')
                                        ->placeholder('Contoh: STORE-01')
                                        ->helperText('Kode cabang/toko jika akun merchant memiliki beberapa cabang.')
                                        ->columnSpan(1),

                                    Textarea::make('paylabs_private_key')
                                        ->label('RSA Private Key Merchant (PKCS#8 PEM)')
                                        ->placeholder("-----BEGIN PRIVATE KEY-----\nMIIEvgIBADANBgkqhkiG9w0BAQEFAASCBKgwggSkAgEAAoIBAQC...\n-----END PRIVATE KEY-----")
                                        ->rows(4)
                                        ->helperText('Kunci rahasia RSA merchant untuk menandatangani request X-SIGNATURE (SHA256withRSA).')
                                        ->columnSpanFull(),

                                    Textarea::make('paylabs_public_key')
                                        ->label('RSA Public Key Paylabs (PEM)')
                                        ->placeholder("-----BEGIN PUBLIC KEY-----\nMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA...\n-----END PUBLIC KEY-----")
                                        ->rows(4)
                                        ->helperText('Kunci publik Paylabs untuk memverifikasi keaslian webhook notifikasi X-SIGNATURE.')
                                        ->columnSpanFull(),
                                ])
                                ->columns(2),

                            Section::make('Saluran Pembayaran Paylabs yang Ditampilkan')
                                ->description('Pilih saluran pembayaran Paylabs yang ingin ditawarkan ke pembeli.')
                                ->icon('heroicon-o-check-badge')
                                ->schema([
                                    CheckboxList::make('paylabs_enabled_channels')
                                        ->label('Saluran Pembayaran Aktif Paylabs')
                                        ->options([
                                            PaylabsGateway::CHANNEL_QRIS       => '📱 Paylabs Dynamic QRIS (Semua E-Wallet & Mobile Banking)',
                                            PaylabsGateway::CHANNEL_BCA_VA     => '🏦 Paylabs BCA Virtual Account',
                                            PaylabsGateway::CHANNEL_BNI_VA     => '🏦 Paylabs BNI Virtual Account',
                                            PaylabsGateway::CHANNEL_BRI_VA     => '🏦 Paylabs BRI Virtual Account (BRIVA)',
                                            PaylabsGateway::CHANNEL_MANDIRI_VA => '🏦 Paylabs Mandiri Virtual Account',
                                            PaylabsGateway::CHANNEL_PERMATA_VA => '🏦 Paylabs Permata Virtual Account',
                                            PaylabsGateway::CHANNEL_CIMB_VA    => '🏦 Paylabs CIMB Niaga Virtual Account',
                                            PaylabsGateway::CHANNEL_DANAMON_VA => '🏦 Paylabs Danamon Virtual Account',
                                            PaylabsGateway::CHANNEL_BSI_VA     => '🏦 Paylabs BSI Virtual Account',
                                        ])
                                        ->columns(2)
                                        ->columnSpanFull(),
                                ]),

                            Section::make('Panduan Webhook Notifikasi Paylabs')
                                ->icon('heroicon-o-arrow-path')
                                ->schema([
                                    Placeholder::make('paylabs_webhook_url')
                                        ->label('Notify URL (Webhook) Paylabs')
                                        ->content(fn () => new HtmlString(
                                            '<div style="background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.1); padding:1rem; border-radius:0.75rem;">' .
                                            '<p style="margin:0 0 0.5rem 0; font-size:0.875rem;">Masukkan URL berikut pada parameter <code>notifyUrl</code> atau daftarkan ke Merchant Dashboard Paylabs agar notifikasi pelunasan otomatis diterima sistem:</p>' .
                                            '<input type="text" readonly value="' . e(route('payment.paylabs.notification')) . '" style="width:100%; background:rgba(0,0,0,0.3); border:1px solid rgba(255,255,255,0.15); padding:0.5rem 0.75rem; border-radius:0.5rem; color:#38BDF8; font-family:monospace; font-weight:bold; margin-bottom:0.75rem;" onclick="this.select()">' .
                                            '<p style="margin:0; font-size:0.8125rem; color:rgba(255,255,255,0.7);">💡 Sistem memverifikasi digital signature RSA-SHA256 pada header <code>X-SIGNATURE</code> dan membalas dengan respon teks <code>SUCCESS</code>.</p>' .
                                            '</div>'
                                        ))
                                        ->columnSpanFull(),
                                ]),

                        ]),

                    // ══════════════════════════════════════════════════════════
                    // TAB 3 — 🏦 Transfer Bank Manual
                    // ══════════════════════════════════════════════════════════
                    Tab::make('🏦  Transfer Bank Manual')
                        ->schema([

                            Section::make('Rekening Bank Toko')
                                ->description('Ditampilkan ke pembeli jika metode Transfer Manual aktif atau saat payment gateway online dimatikan.')
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
