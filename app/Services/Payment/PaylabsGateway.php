<?php

namespace App\Services\Payment;

use App\Contracts\PaymentGateway;
use App\Exceptions\PaymentGatewayException;
use App\Models\Order;
use App\Settings\PaymentSettings;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Paylabs Direct Payment Gateway (Core API integration).
 *
 * Direct integration via Paylabs API v2.3:
 * - Dynamic QRIS creation (/payment/v2.3/qris/create).
 * - Virtual Account generation (/payment/v2.3/va/create) for BCA, BNI, BRI, Mandiri, Permata, CIMB, Danamon, BSI.
 * - Digital signature generation with RSA-SHA256 (PKCS#8).
 * - Automatic status polling (/payment/v2.3/qris/query or /payment/v2.3/va/query).
 * - Webhook notification processing (/payment/paylabs/notification).
 */
class PaylabsGateway implements PaymentGateway
{
    public const KEY = 'paylabs';

    public const CHANNEL_QRIS       = 'paylabs_qris';
    public const CHANNEL_BCA_VA     = 'paylabs_bca_va';
    public const CHANNEL_BNI_VA     = 'paylabs_bni_va';
    public const CHANNEL_BRI_VA     = 'paylabs_bri_va';
    public const CHANNEL_MANDIRI_VA = 'paylabs_mandiri_va';
    public const CHANNEL_PERMATA_VA = 'paylabs_permata_va';
    public const CHANNEL_CIMB_VA    = 'paylabs_cimb_va';
    public const CHANNEL_DANAMON_VA = 'paylabs_danamon_va';
    public const CHANNEL_BSI_VA     = 'paylabs_bsi_va';

    public const ALL_CHANNELS = [
        self::CHANNEL_QRIS => [
            'id'          => self::CHANNEL_QRIS,
            'name'        => 'QRIS (Paylabs)',
            'label'       => 'QRIS (Semua E-Wallet & Mobile Banking)',
            'description' => 'Scan langsung via BCA Mobile, Livin, BRImo, BNI, GoPay, OVO, DANA, ShopeePay, LinkAja, dll.',
            'badge'       => 'Instan',
            'category'    => 'qris',
            'paylabs_code'=> 'QRIS',
        ],
        self::CHANNEL_BCA_VA => [
            'id'          => self::CHANNEL_BCA_VA,
            'name'        => 'BCA Virtual Account (Paylabs)',
            'label'       => 'BCA Virtual Account',
            'description' => 'Bayar melalui BCA Mobile, KlikBCA, atau ATM BCA.',
            'badge'       => 'Verifikasi Otomatis',
            'category'    => 'va',
            'paylabs_code'=> 'BCAVA',
        ],
        self::CHANNEL_BNI_VA => [
            'id'          => self::CHANNEL_BNI_VA,
            'name'        => 'BNI Virtual Account (Paylabs)',
            'label'       => 'BNI Virtual Account',
            'description' => 'Bayar melalui BNI Mobile Banking atau ATM BNI.',
            'badge'       => 'Verifikasi Otomatis',
            'category'    => 'va',
            'paylabs_code'=> 'BNIVA',
        ],
        self::CHANNEL_BRI_VA => [
            'id'          => self::CHANNEL_BRI_VA,
            'name'        => 'BRI Virtual Account (Paylabs)',
            'label'       => 'BRI Virtual Account (BRIVA)',
            'description' => 'Bayar melalui BRImo atau ATM BRI.',
            'badge'       => 'Verifikasi Otomatis',
            'category'    => 'va',
            'paylabs_code'=> 'BRIVA',
        ],
        self::CHANNEL_MANDIRI_VA => [
            'id'          => self::CHANNEL_MANDIRI_VA,
            'name'        => 'Mandiri Virtual Account (Paylabs)',
            'label'       => 'Mandiri Virtual Account',
            'description' => 'Bayar melalui Livin by Mandiri atau ATM Mandiri.',
            'badge'       => 'Verifikasi Otomatis',
            'category'    => 'va',
            'paylabs_code'=> 'MANDIRIVA',
        ],
        self::CHANNEL_PERMATA_VA => [
            'id'          => self::CHANNEL_PERMATA_VA,
            'name'        => 'Permata Virtual Account (Paylabs)',
            'label'       => 'Permata Virtual Account',
            'description' => 'Bayar melalui PermataMobile X atau ATM Permata/transfer jaringan ATM Bersama.',
            'badge'       => 'Verifikasi Otomatis',
            'category'    => 'va',
            'paylabs_code'=> 'PERMATAVA',
        ],
        self::CHANNEL_CIMB_VA => [
            'id'          => self::CHANNEL_CIMB_VA,
            'name'        => 'CIMB Niaga Virtual Account (Paylabs)',
            'label'       => 'CIMB Niaga Virtual Account',
            'description' => 'Bayar melalui OCTO Mobile, OCTO Clicks, atau ATM CIMB Niaga.',
            'badge'       => 'Verifikasi Otomatis',
            'category'    => 'va',
            'paylabs_code'=> 'CIMBVA',
        ],
        self::CHANNEL_DANAMON_VA => [
            'id'          => self::CHANNEL_DANAMON_VA,
            'name'        => 'Danamon Virtual Account (Paylabs)',
            'label'       => 'Danamon Virtual Account',
            'description' => 'Bayar melalui D-Bank PRO atau ATM Bank Danamon.',
            'badge'       => 'Verifikasi Otomatis',
            'category'    => 'va',
            'paylabs_code'=> 'DANAMONVA',
        ],
        self::CHANNEL_BSI_VA => [
            'id'          => self::CHANNEL_BSI_VA,
            'name'        => 'BSI Virtual Account (Paylabs)',
            'label'       => 'BSI Virtual Account',
            'description' => 'Bayar melalui BSI Mobile atau ATM Bank Syariah Indonesia.',
            'badge'       => 'Verifikasi Otomatis',
            'category'    => 'va',
            'paylabs_code'=> 'BSIVA',
        ],
    ];

    public function key(): string
    {
        return self::KEY;
    }

    public function name(): string
    {
        return 'Paylabs Payment Gateway';
    }

    public function isConfigured(): bool
    {
        return filled($this->getMerchantId()) && filled($this->getPrivateKey());
    }

    public function getMerchantId(): string
    {
        try {
            $settings = app(PaymentSettings::class);
            return (string) ($settings->paylabs_merchant_id ?: config('services.paylabs.merchant_id'));
        } catch (\Throwable) {
            return (string) config('services.paylabs.merchant_id', '');
        }
    }

    public function getStoreId(): ?string
    {
        try {
            $settings = app(PaymentSettings::class);
            return $settings->paylabs_store_id ?: config('services.paylabs.store_id');
        } catch (\Throwable) {
            return (string) config('services.paylabs.store_id', '');
        }
    }

    public function getPrivateKey(): string
    {
        try {
            $settings = app(PaymentSettings::class);
            return (string) ($settings->paylabs_private_key ?: config('services.paylabs.private_key'));
        } catch (\Throwable) {
            return (string) config('services.paylabs.private_key', '');
        }
    }

    public function getPublicKey(): string
    {
        try {
            $settings = app(PaymentSettings::class);
            return (string) ($settings->paylabs_public_key ?: config('services.paylabs.public_key'));
        } catch (\Throwable) {
            return (string) config('services.paylabs.public_key', '');
        }
    }

    public function isProduction(): bool
    {
        try {
            $settings = app(PaymentSettings::class);
            return (bool) ($settings->paylabs_is_production ?? config('services.paylabs.is_production', false));
        } catch (\Throwable) {
            return (bool) config('services.paylabs.is_production', false);
        }
    }

    public function getBaseUrl(): string
    {
        return $this->isProduction()
            ? 'https://pay.paylabs.co.id'
            : 'https://sit-pay.paylabs.co.id';
    }

    /**
     * @return array<string, array<string, string>>
     */
    public function enabledChannels(): array
    {
        try {
            $settings = app(PaymentSettings::class);
            $enabled = $settings->paylabs_enabled_channels ?? [];
            if (!empty($enabled) && is_array($enabled)) {
                $filtered = array_intersect_key(self::ALL_CHANNELS, array_flip($enabled));
                if (!empty($filtered)) {
                    return $filtered;
                }
            }
        } catch (\Throwable) {
        }

        return self::ALL_CHANNELS;
    }

    /**
     * Entry point for OrderController@pending.
     *
     * @return array<string, mixed>
     */
    public function charge(Order $order): array
    {
        $amount = $order->payableAmount();

        // If order already has an active Paylabs payload, render it directly
        if (filled($order->paylabs_payment_payload) && filled($order->paylabs_payment_type)) {
            $channel = (string) $order->paylabs_payment_type;
            $payload = (array) $order->paylabs_payment_payload;
            $channelInfo = self::ALL_CHANNELS[$channel] ?? ['name' => ucfirst($channel), 'label' => ucfirst($channel)];

            return [
                'type'             => 'core_api',
                'gateway'          => self::KEY,
                'name'             => $channelInfo['name'],
                'configured'       => true,
                'amount'           => $amount,
                'formatted_amount' => Order::formatRupiah($amount),
                'reference'        => $order->order_number,
                'channel'          => $channel,
                'channel_info'     => $channelInfo,
                'payload'          => $payload,
                'instructions'     => $this->instructionsFor($channel, $payload, $order),
            ];
        }

        return [
            'type'             => 'channel_selection',
            'gateway'          => self::KEY,
            'name'             => 'Paylabs Pembayaran Online',
            'configured'       => $this->isConfigured(),
            'amount'           => $amount,
            'formatted_amount' => Order::formatRupiah($amount),
            'reference'        => $order->order_number,
            'channels'         => $this->enabledChannels(),
        ];
    }

    /**
     * Direct charge for chosen Paylabs channel (QRIS or Virtual Account).
     *
     * @return array<string, mixed>
     */
    public function chargeChannel(Order $order, string $channel): array
    {
        $amount = $order->payableAmount();

        if (! array_key_exists($channel, self::ALL_CHANNELS)) {
            throw new PaymentGatewayException('Metode pembayaran Paylabs tidak didukung: ' . $channel, self::KEY);
        }

        $channelInfo = self::ALL_CHANNELS[$channel];
        $paylabsCode = $channelInfo['paylabs_code'];

        if ($channel === self::CHANNEL_QRIS && $amount > 10000000) {
            throw new PaymentGatewayException('Metode pembayaran QRIS memiliki batas nominal maksimal Rp 10.000.000 (sesuai regulasi Bank Indonesia). Silakan gunakan metode Virtual Account (VA) untuk nominal ini.', self::KEY);
        }

        // Fallback simulation mode jika kredensial belum dikonfigurasi sama sekali (mode demo lokal)
        if (! $this->isConfigured()) {
            $parsed = $this->createSimulatedCharge($order, $channel, $amount);
        } else {
            // Ketika kredensial sudah diatur, gunakan API Paylabs resmi secara langsung
            $parsed = $this->sendChargeRequest($order, $channel, $paylabsCode, $amount);
        }

        $order->update([
            'payment_method'          => self::KEY,
            'paylabs_transaction_id'  => $parsed['transaction_id'] ?? null,
            'paylabs_payment_type'    => $channel,
            'paylabs_payment_payload' => $parsed,
        ]);

        return [
            'type'             => 'core_api',
            'gateway'          => self::KEY,
            'name'             => $channelInfo['name'],
            'configured'       => true,
            'amount'           => $amount,
            'formatted_amount' => Order::formatRupiah($amount),
            'reference'        => $order->order_number,
            'channel'          => $channel,
            'channel_info'     => $channelInfo,
            'payload'          => $parsed,
            'instructions'     => $this->instructionsFor($channel, $parsed, $order),
        ];
    }

    /**
     * Send HTTP POST to Paylabs API v2.3.
     *
     * @return array<string, mixed>
     */
    protected function sendChargeRequest(Order $order, string $channel, string $paylabsCode, float $amount): array
    {
        $merchantId = $this->getMerchantId();
        $storeId    = $this->getStoreId();
        $isQris     = ($channel === self::CHANNEL_QRIS);
        $endpoint   = $isQris ? '/payment/v2.3/qris/create' : '/payment/v2.3/va/create';
        $requestId  = 'REQ-' . $order->order_number . '-' . time();
        $timestamp  = (new \DateTime('now', new \DateTimeZone('Asia/Jakarta')))->format('Y-m-d\TH:i:s.vP');

        $body = [
            'requestId'       => $requestId,
            'merchantId'      => $merchantId,
            'paymentType'     => $paylabsCode,
            'amount'          => number_format($amount, 2, '.', ''),
            'merchantTradeNo' => $order->order_number,
            'productName'     => mb_substr((string) ($order->product_name_snapshot ?? 'Pesanan ' . $order->order_number), 0, 95),
            'notifyUrl'       => route('payment.paylabs.notification'),
        ];

        if (filled($storeId)) {
            $body['storeId'] = $storeId;
        }

        if (! $isQris) {
            $body['payer'] = $order->customer_name ?: 'Pelanggan RBeverything';
        }

        $minified = json_encode($body, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $signature = $this->generateSignature('POST', $endpoint, $body, $timestamp, $this->getPrivateKey());

        $headers = [
            'Content-Type'  => 'application/json;charset=utf-8',
            'X-TIMESTAMP'   => $timestamp,
            'X-PARTNER-ID'  => $merchantId,
            'X-REQUEST-ID'  => $requestId,
            'X-SIGNATURE'   => $signature,
        ];

        $res = Http::withHeaders($headers)
            ->withBody($minified, 'application/json;charset=utf-8')
            ->timeout(12)
            ->post($this->getBaseUrl() . $endpoint);

        if (! $res->successful()) {
            throw new PaymentGatewayException(
                'Paylabs API merespon error HTTP ' . $res->status() . ': ' . $res->body(),
                self::KEY
            );
        }

        $data = $res->json() ?: [];
        $errCode = (string) ($data['errCode'] ?? '');

        if ($errCode !== '0') {
            $errDes = (string) ($data['errCodeDes'] ?? 'Kode error Paylabs: ' . $errCode);
            throw new PaymentGatewayException('Paylabs menolak transaksi: ' . $errDes, self::KEY);
        }

        return $this->parseChargeResponse($data, $channel, $amount);
    }

    /**
     * Digital Signature Generator for Paylabs API (RSA-SHA256).
     */
    public function generateSignature(
        string $method,
        string $endpoint,
        array $body,
        string $timestamp,
        string $privateKeyPem
    ): string {
        $minified = json_encode($body, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $bodyHash = hash('sha256', $minified);
        $stringToSign = strtoupper($method) . ':' . $endpoint . ':' . strtolower($bodyHash) . ':' . $timestamp;

        $key = openssl_pkey_get_private($this->formatPrivateKey($privateKeyPem));
        if (! $key) {
            throw new PaymentGatewayException('Format RSA Private Key Paylabs tidak valid.', self::KEY);
        }

        $binarySignature = '';
        $signed = openssl_sign($stringToSign, $binarySignature, $key, OPENSSL_ALGO_SHA256);

        if (! $signed) {
            throw new PaymentGatewayException('Gagal melakukan generate signature RSA Paylabs.', self::KEY);
        }

        return base64_encode($binarySignature);
    }

    /**
     * Digital Signature Verifier for Paylabs Webhooks (RSA-SHA256).
     */
    public function verifySignature(
        string $method,
        string $endpoint,
        string $rawBody,
        string $timestamp,
        string $signatureBase64,
        string $publicKeyPem
    ): bool {
        $bodyHash = hash('sha256', $rawBody);
        $stringToSign = strtoupper($method) . ':' . $endpoint . ':' . strtolower($bodyHash) . ':' . $timestamp;

        $key = openssl_pkey_get_public($this->formatPublicKey($publicKeyPem));
        if (! $key) {
            Log::warning('Format RSA Public Key Paylabs tidak valid untuk verifikasi webhook.');
            return false;
        }

        $binarySignature = base64_decode($signatureBase64);
        return openssl_verify($stringToSign, $binarySignature, $key, OPENSSL_ALGO_SHA256) === 1;
    }

    /**
     * Query live transaction status from Paylabs API.
     *
     * @return array<string, mixed>
     */
    public function checkStatus(string $orderNumber): array
    {
        if (! $this->isConfigured()) {
            return ['status' => 'pending'];
        }

        $merchantId = $this->getMerchantId();
        $requestId  = 'QRY-' . $orderNumber . '-' . time();
        $timestamp  = (new \DateTime('now', new \DateTimeZone('Asia/Jakarta')))->format('Y-m-d\TH:i:s.vP');

        $body = [
            'merchantId'      => $merchantId,
            'requestId'       => $requestId,
            'merchantTradeNo' => $orderNumber,
        ];

        // Cek status QRIS atau VA
        $endpoint = '/payment/v2.3/qris/query';
        $minified = json_encode($body, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $signature = $this->generateSignature('POST', $endpoint, $body, $timestamp, $this->getPrivateKey());

        $headers = [
            'Content-Type'  => 'application/json;charset=utf-8',
            'X-TIMESTAMP'   => $timestamp,
            'X-PARTNER-ID'  => $merchantId,
            'X-REQUEST-ID'  => $requestId,
            'X-SIGNATURE'   => $signature,
        ];

        try {
            $res = Http::withHeaders($headers)
                ->withBody($minified, 'application/json;charset=utf-8')
                ->timeout(10)
                ->post($this->getBaseUrl() . $endpoint);

            if ($res->successful()) {
                $data = $res->json();
                if (($data['errCode'] ?? '') === '0') {
                    return $data;
                }
            }

            // Coba endpoint VA query jika QRIS query not found
            $endpointVa = '/payment/v2.3/va/query';
            $sigVa = $this->generateSignature('POST', $endpointVa, $body, $timestamp, $this->getPrivateKey());
            $headers['X-SIGNATURE'] = $sigVa;

            $resVa = Http::withHeaders($headers)
                ->withBody($minified, 'application/json;charset=utf-8')
                ->timeout(10)
                ->post($this->getBaseUrl() . $endpointVa);

            return $resVa->json() ?: [];
        } catch (\Throwable $e) {
            Log::warning('Paylabs checkStatus query failed', [
                'order_number' => $orderNumber,
                'error'        => $e->getMessage(),
            ]);

            throw new PaymentGatewayException('Gagal memeriksa status ke Paylabs: ' . $e->getMessage(), self::KEY, $e);
        }
    }

    /**
     * Parse Paylabs API charge response to unified internal format.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    protected function parseChargeResponse(array $data, string $channel, float $amount): array
    {
        $platformTradeNo = $data['platformTradeNo'] ?? null;
        $qrUrl           = $data['qrUrl'] ?? ($data['qrisUrl'] ?? null);
        $qrContent       = $data['qrContent'] ?? ($data['qrCode'] ?? null);
        $qrImageUrl      = null;

        // Prioritize official hosted QR image URL from Paylabs if returned
        if (filled($qrUrl) && filter_var($qrUrl, FILTER_VALIDATE_URL)) {
            $qrImageUrl = $qrUrl;
        } elseif (filled($qrContent)) {
            $qrImageUrl = filter_var($qrContent, FILTER_VALIDATE_URL)
                ? $qrContent
                : 'https://api.qrserver.com/v1/create-qr-code/?size=280x280&data=' . urlencode($qrContent);
        }

        $vaNumber = $data['vaCode'] ?? ($data['vaNumber'] ?? null);

        return [
            'status_code'        => '200',
            'status_message'     => $data['errCodeDes'] ?? 'Success',
            'transaction_id'     => $platformTradeNo,
            'transaction_status' => 'pending',
            'channel'            => $channel,
            'qr_string'          => $qrContent,
            'qr_url'             => $qrImageUrl,
            'va_number'          => $vaNumber,
            'bank'               => str_replace(['paylabs_', '_va'], '', $channel),
            'gross_amount'       => number_format($amount, 2, '.', ''),
            'expiry_time'        => $data['expiredTime'] ?? null,
            'raw_response'       => $data,
        ];
    }

    /**
     * Step-by-step payment instructions.
     *
     * @param array<string, mixed> $payload
     * @return array<string>
     */
    protected function instructionsFor(string $channel, array $payload, Order $order): array
    {
        $amountStr = Order::formatRupiah($order->payableAmount());
        $vaNumber  = $payload['va_number'] ?? '-';

        return match ($channel) {
            self::CHANNEL_QRIS => [
                'Buka aplikasi mobile banking (BCA, Mandiri, BRI, BNI) atau e-wallet (GoPay, OVO, DANA, ShopeePay, LinkAja).',
                'Pilih menu Scan QR / QRIS.',
                'Arahkan kamera ke QR Code di atas atau upload tangkapan layar (screenshot) QR code.',
                'Periksa nominal pembayaran sebesar ' . $amountStr . ' dan selesaikan transaksi.',
                'Pembayaran via Paylabs QRIS akan terverifikasi otomatis dalam beberapa detik.',
            ],
            self::CHANNEL_BCA_VA => [
                'Buka aplikasi BCA Mobile, KlikBCA, atau kunjungi ATM BCA.',
                'Pilih menu Transfer > BCA Virtual Account.',
                'Masukkan Nomor Virtual Account Paylabs: ' . $vaNumber,
                'Pastikan nama penerima dan tagihan sebesar ' . $amountStr . ' sesuai.',
                'Konfirmasi pembayaran dan masukkan PIN Anda.',
            ],
            self::CHANNEL_BNI_VA => [
                'Buka BNI Mobile Banking atau ATM BNI.',
                'Pilih Pembayaran / Transfer > Virtual Account Billing.',
                'Masukkan Nomor Virtual Account: ' . $vaNumber,
                'Periksa tagihan sebesar ' . $amountStr . ' dan masukkan PIN.',
            ],
            self::CHANNEL_BRI_VA => [
                'Buka aplikasi BRImo atau kunjungi ATM BRI.',
                'Pilih Pembayaran > BRIVA.',
                'Masukkan Nomor Virtual Account: ' . $vaNumber,
                'Konfirmasi pembayaran dan masukkan PIN transaksi Anda.',
            ],
            self::CHANNEL_MANDIRI_VA => [
                'Buka Livin by Mandiri atau ATM Mandiri.',
                'Pilih menu Bayar / Pembayaran > Virtual Account.',
                'Masukkan Nomor Virtual Account Mandiri: ' . $vaNumber,
                'Periksa rincian tagihan ' . $amountStr . ' dan konfirmasi transaksi.',
            ],
            self::CHANNEL_PERMATA_VA => [
                'Buka PermataMobile X atau ATM Permata / ATM Bersama.',
                'Pilih Transfer > Rekening Virtual Account.',
                'Masukkan Nomor Virtual Account Permata: ' . $vaNumber,
                'Periksa rincian tagihan dan konfirmasi pembayaran.',
            ],
            self::CHANNEL_CIMB_VA => [
                'Buka OCTO Mobile, OCTO Clicks, atau ATM CIMB Niaga.',
                'Pilih Transfer > Rekening Virtual Account CIMB Niaga.',
                'Masukkan Nomor Virtual Account: ' . $vaNumber,
                'Selesaikan pembayaran dengan memasukkan PIN.',
            ],
            self::CHANNEL_DANAMON_VA => [
                'Buka aplikasi D-Bank PRO atau ATM Bank Danamon.',
                'Pilih Pembayaran / Virtual Account.',
                'Masukkan Nomor Virtual Account Danamon: ' . $vaNumber,
                'Periksa nominal dan selesaikan transaksi.',
            ],
            self::CHANNEL_BSI_VA => [
                'Buka aplikasi BSI Mobile atau ATM Bank Syariah Indonesia.',
                'Pilih Pembayaran > Rekening Virtual Account BSI.',
                'Masukkan Nomor Virtual Account: ' . $vaNumber,
                'Selesaikan pembayaran sesuai petunjuk.',
            ],
            default => [
                'Selesaikan pembayaran sebesar ' . $amountStr . ' sesuai metode yang dipilih.',
                'Status pesanan akan terverifikasi secara otomatis begitu dana diterima oleh Paylabs.',
            ],
        };
    }

    /**
     * Local Mock Simulator for offline testing / development.
     *
     * @return array<string, mixed>
     */
    protected function createSimulatedCharge(Order $order, string $channel, float $amount): array
    {
        $platformTradeNo = 'PL-SIM-' . strtoupper(Str::random(10));
        $isQris = ($channel === self::CHANNEL_QRIS);

        $bankCode = match ($channel) {
            self::CHANNEL_BCA_VA     => 'BCA',
            self::CHANNEL_BNI_VA     => 'BNI',
            self::CHANNEL_BRI_VA     => 'BRI',
            self::CHANNEL_MANDIRI_VA => 'MANDIRI',
            self::CHANNEL_PERMATA_VA => 'PERMATA',
            self::CHANNEL_CIMB_VA    => 'CIMB',
            self::CHANNEL_DANAMON_VA => 'DANAMON',
            self::CHANNEL_BSI_VA     => 'BSI',
            default                  => 'BANK',
        };

        $vaNumber = '9888' . rand(10000000, 99999999);
        $qrString = '00020101021226660014ID.LINKAJA.WWW01189360091800000000010215' . rand(10000000, 99999999) . '5204581253033605802ID5912RBeverything6007Jakarta6304';
        $qrImageUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=280x280&data=' . urlencode($qrString);

        return [
            'status_code'        => '200',
            'status_message'     => 'Paylabs Simulation Mode (Kredensial belum diisi)',
            'transaction_id'     => $platformTradeNo,
            'transaction_status' => 'pending',
            'channel'            => $channel,
            'qr_string'          => $isQris ? $qrString : null,
            'qr_url'             => $isQris ? $qrImageUrl : null,
            'va_number'          => $isQris ? null : $vaNumber,
            'bank'               => $bankCode,
            'gross_amount'       => number_format($amount, 2, '.', ''),
            'expiry_time'        => date('Y-m-d H:i:s', strtotime('+1 day')),
            'is_simulated'       => true,
        ];
    }

    /**
     * Format private key with standard PEM headers if missing.
     */
    protected function formatPrivateKey(string $key): string
    {
        $key = trim($key);
        if (! str_contains($key, '-----BEGIN')) {
            $key = "-----BEGIN RSA PRIVATE KEY-----\n" . wordwrap($key, 64, "\n", true) . "\n-----END RSA PRIVATE KEY-----";
        }
        return $key;
    }

    /**
     * Format public key with standard PEM headers if missing.
     */
    protected function formatPublicKey(string $key): string
    {
        $key = trim($key);
        if (! str_contains($key, '-----BEGIN')) {
            $key = "-----BEGIN PUBLIC KEY-----\n" . wordwrap($key, 64, "\n", true) . "\n-----END PUBLIC KEY-----";
        }
        return $key;
    }
}
