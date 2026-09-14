<?php

namespace App\Services\Payment;

use App\Contracts\PaymentGateway;
use App\Exceptions\PaymentGatewayException;
use App\Models\Order;
use App\Settings\PaymentSettings;
use Illuminate\Support\Facades\Log;
use Midtrans\Config as MidtransConfig;
use Midtrans\CoreApi;
use Midtrans\Transaction;

/**
 * Midtrans Core API (Direct Payment Gateway).
 *
 * Direct integration via Midtrans Core API (/v2/charge):
 * - Eliminates Snap hosted redirects/popups.
 * - Renders dynamic QRIS QR Code directly on the site.
 * - Renders Virtual Account numbers (BCA, BNI, BRI, Permata) and Mandiri Bill directly.
 * - Status is handled via Webhook (/payment/midtrans/notification) and on-demand status check (/v2/{id}/status).
 */
class MidtransGateway implements PaymentGateway
{
    public const KEY = 'midtrans';

    public const CHANNEL_QRIS         = 'qris';
    public const CHANNEL_BCA_VA       = 'bca_va';
    public const CHANNEL_BNI_VA       = 'bni_va';
    public const CHANNEL_BRI_VA       = 'bri_va';
    public const CHANNEL_MANDIRI_BILL = 'mandiri_bill';
    public const CHANNEL_PERMATA_VA   = 'permata_va';
    public const CHANNEL_CIMB_VA      = 'cimb_va';
    public const CHANNEL_GOPAY        = 'gopay';
    public const CHANNEL_SHOPEEPAY    = 'shopeepay';
    public const CHANNEL_INDOMARET    = 'indomaret';
    public const CHANNEL_ALFAMART     = 'alfamart';

    public const ALL_CHANNELS = [
        self::CHANNEL_QRIS => [
            'id'          => self::CHANNEL_QRIS,
            'name'        => 'QRIS',
            'label'       => 'QRIS (Semua E-Wallet & Mobile Banking)',
            'description' => 'Scan dengan BCA Mobile, Livin by Mandiri, BRImo, BNI, GoPay, OVO, DANA, ShopeePay, LinkAja, dll.',
            'badge'       => 'Instan',
            'category'    => 'qris',
        ],
        self::CHANNEL_BCA_VA => [
            'id'          => self::CHANNEL_BCA_VA,
            'name'        => 'BCA Virtual Account',
            'label'       => 'BCA Virtual Account',
            'description' => 'Bayar via BCA Mobile, KlikBCA, atau ATM BCA.',
            'badge'       => 'Verifikasi Otomatis',
            'category'    => 'va',
        ],
        self::CHANNEL_BNI_VA => [
            'id'          => self::CHANNEL_BNI_VA,
            'name'        => 'BNI Virtual Account',
            'label'       => 'BNI Virtual Account',
            'description' => 'Bayar via BNI Mobile Banking, BNI Internet Banking, atau ATM BNI.',
            'badge'       => 'Verifikasi Otomatis',
            'category'    => 'va',
        ],
        self::CHANNEL_BRI_VA => [
            'id'          => self::CHANNEL_BRI_VA,
            'name'        => 'BRI Virtual Account (BRIVA)',
            'label'       => 'BRI Virtual Account (BRIVA)',
            'description' => 'Bayar via BRImo, Internet Banking BRI, atau ATM BRI.',
            'badge'       => 'Verifikasi Otomatis',
            'category'    => 'va',
        ],
        self::CHANNEL_MANDIRI_BILL => [
            'id'          => self::CHANNEL_MANDIRI_BILL,
            'name'        => 'Mandiri Bill Payment',
            'label'       => 'Mandiri Bill Payment',
            'description' => 'Bayar via Livin by Mandiri atau ATM Mandiri.',
            'badge'       => 'Verifikasi Otomatis',
            'category'    => 'bill',
        ],
        self::CHANNEL_PERMATA_VA => [
            'id'          => self::CHANNEL_PERMATA_VA,
            'name'        => 'Permata Virtual Account',
            'label'       => 'Permata Virtual Account',
            'description' => 'Bayar via PermataMobile X atau ATM Permata/transfer bank lain.',
            'badge'       => 'Verifikasi Otomatis',
            'category'    => 'va',
        ],
        self::CHANNEL_CIMB_VA => [
            'id'          => self::CHANNEL_CIMB_VA,
            'name'        => 'CIMB Niaga Virtual Account',
            'label'       => 'CIMB Niaga Virtual Account',
            'description' => 'Bayar via OCTO Mobile, OCTO Clicks, atau ATM CIMB Niaga / ATM Bersama.',
            'badge'       => 'Verifikasi Otomatis',
            'category'    => 'va',
        ],
        self::CHANNEL_GOPAY => [
            'id'          => self::CHANNEL_GOPAY,
            'name'        => 'GoPay (Direct / QR)',
            'label'       => 'GoPay (Direct Deeplink & QR)',
            'description' => 'Langsung buka aplikasi Gojek di ponsel atau scan QR Code.',
            'badge'       => 'Instan',
            'category'    => 'ewallet',
        ],
        self::CHANNEL_SHOPEEPAY => [
            'id'          => self::CHANNEL_SHOPEEPAY,
            'name'        => 'ShopeePay (Direct / QR)',
            'label'       => 'ShopeePay (Direct Deeplink & QR)',
            'description' => 'Langsung buka aplikasi Shopee di ponsel atau scan QR Code.',
            'badge'       => 'Instan',
            'category'    => 'ewallet',
        ],
        self::CHANNEL_INDOMARET => [
            'id'          => self::CHANNEL_INDOMARET,
            'name'        => 'Indomaret / Ceriamart',
            'label'       => 'Indomaret / Ceriamart',
            'description' => 'Bayar tunai di kasir gerai Indomaret terdekat dengan Kode Pembayaran.',
            'badge'       => 'Gerai Retail',
            'category'    => 'cstore',
        ],
        self::CHANNEL_ALFAMART => [
            'id'          => self::CHANNEL_ALFAMART,
            'name'        => 'Alfamart / Alfamidi / Dan+Dan',
            'label'       => 'Alfamart / Alfamidi / Dan+Dan',
            'description' => 'Bayar tunai di kasir gerai Alfamart terdekat dengan Kode Pembayaran.',
            'badge'       => 'Gerai Retail',
            'category'    => 'cstore',
        ],
    ];

    public function key(): string
    {
        return self::KEY;
    }

    public function name(): string
    {
        return 'Pembayaran Online';
    }

    public function isConfigured(): bool
    {
        return filled($this->getServerKey());
    }

    public function getServerKey(): string
    {
        try {
            $settings = app(PaymentSettings::class);
            return (string) ($settings->midtrans_server_key ?: config('services.midtrans.server_key'));
        } catch (\Throwable) {
            return (string) config('services.midtrans.server_key', '');
        }
    }

    public function getClientKey(): string
    {
        try {
            $settings = app(PaymentSettings::class);
            return (string) ($settings->midtrans_client_key ?: config('services.midtrans.client_key'));
        } catch (\Throwable) {
            return (string) config('services.midtrans.client_key', '');
        }
    }

    public function isProduction(): bool
    {
        try {
            $settings = app(PaymentSettings::class);
            return (bool) ($settings->midtrans_is_production ?? config('services.midtrans.is_production', false));
        } catch (\Throwable) {
            return (bool) config('services.midtrans.is_production', false);
        }
    }

    /**
     * @return array<string, array<string, string>>
     */
    public function enabledChannels(): array
    {
        try {
            $settings = app(PaymentSettings::class);
            $enabled = $settings->midtrans_enabled_channels ?? [];
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
     * If the order has already been charged with a specific channel and has stored payload,
     * return that directly so the buyer can see their VA number or QRIS code.
     * Otherwise, return channel selection options.
     *
     * @return array<string, mixed>
     */
    public function charge(Order $order): array
    {
        if (! $this->isConfigured()) {
            throw new PaymentGatewayException('Midtrans server key belum dikonfigurasi.', self::KEY);
        }

        $amount = $order->payableAmount();

        // If this order already has an active Midtrans charge payload, show it!
        if (filled($order->midtrans_payment_payload) && filled($order->midtrans_payment_type)) {
            $channel = (string) $order->midtrans_payment_type;
            $payload = (array) $order->midtrans_payment_payload;
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

        // Otherwise, ask the buyer to select a payment channel
        return [
            'type'             => 'channel_selection',
            'gateway'          => self::KEY,
            'name'             => 'Pembayaran Online',
            'configured'       => true,
            'amount'           => $amount,
            'formatted_amount' => Order::formatRupiah($amount),
            'reference'        => $order->order_number,
            'channels'         => $this->enabledChannels(),
        ];
    }

    /**
     * Perform direct charge to Midtrans Core API (/v2/charge) for a specific payment channel.
     *
     * @return array<string, mixed>
     */
    public function chargeChannel(Order $order, string $channel): array
    {
        if (! $this->isConfigured()) {
            throw new PaymentGatewayException('Midtrans server key belum dikonfigurasi.', self::KEY);
        }

        if (! array_key_exists($channel, self::ALL_CHANNELS)) {
            throw new PaymentGatewayException('Metode pembayaran tidak didukung: ' . $channel, self::KEY);
        }

        $this->applyConfig();
        $amount = $order->payableAmount();

        $params = $this->buildCoreApiParams($order, $amount, $channel);

        try {
            $response = CoreApi::charge($params);
            $responseArray = json_decode(json_encode($response), true) ?: [];
        } catch (\Throwable $e) {
            Log::error('Midtrans Core API charge failed', [
                'order_number' => $order->order_number,
                'channel'      => $channel,
                'error'        => $e->getMessage(),
            ]);

            throw new PaymentGatewayException(
                'Midtrans menolak transaksi: ' . $e->getMessage(),
                self::KEY,
                $e,
            );
        }

        $parsed = $this->parseChargeResponse($responseArray, $channel);

        $order->update([
            'payment_method'           => self::KEY,
            'midtrans_transaction_id'  => $parsed['transaction_id'] ?? null,
            'midtrans_payment_type'    => $channel,
            'midtrans_payment_payload' => $parsed,
        ]);

        return [
            'type'             => 'core_api',
            'gateway'          => self::KEY,
            'name'             => self::ALL_CHANNELS[$channel]['name'] ?? $channel,
            'configured'       => true,
            'amount'           => $amount,
            'formatted_amount' => Order::formatRupiah($amount),
            'reference'        => $order->order_number,
            'channel'          => $channel,
            'channel_info'     => self::ALL_CHANNELS[$channel],
            'payload'          => $parsed,
            'instructions'     => $this->instructionsFor($channel, $parsed, $order),
        ];
    }

    /**
     * Query live transaction status from Midtrans Core API.
     *
     * @return array<string, mixed>
     */
    public function checkStatus(string $orderNumber): array
    {
        $this->applyConfig();

        try {
            $res = Transaction::status($orderNumber);
            return json_decode(json_encode($res), true) ?: [];
        } catch (\Throwable $e) {
            Log::warning('Midtrans checkStatus query failed', [
                'order_number' => $orderNumber,
                'error'        => $e->getMessage(),
            ]);

            throw new PaymentGatewayException(
                'Gagal memeriksa status ke Midtrans: ' . $e->getMessage(),
                self::KEY,
                $e,
            );
        }
    }

    /**
     * Cancel an active transaction on Midtrans Core API.
     *
     * @return array<string, mixed>
     */
    public function cancelTransaction(string $orderNumber): array
    {
        $this->applyConfig();

        try {
            $res = Transaction::cancel($orderNumber);
            return json_decode(json_encode($res), true) ?: [];
        } catch (\Throwable $e) {
            Log::warning('Midtrans cancelTransaction failed', [
                'order_number' => $orderNumber,
                'error'        => $e->getMessage(),
            ]);

            throw new PaymentGatewayException(
                'Gagal membatalkan transaksi di Midtrans: ' . $e->getMessage(),
                self::KEY,
                $e,
            );
        }
    }

    /**
     * Build Core API parameter payload according to Midtrans specifications.
     *
     * @return array<string, mixed>
     */
    protected function buildCoreApiParams(Order $order, float $amount, string $channel): array
    {
        $base = [
            'transaction_details' => [
                'order_id'     => $order->order_number,
                'gross_amount' => (int) round($amount),
            ],
            'customer_details' => [
                'first_name' => $order->customer_name,
                'email'      => $order->customer_email,
                'phone'      => $order->customer_phone,
            ],
            'item_details' => [
                [
                    'id'       => (string) ($order->product_id ?? 'custom'),
                    'price'    => (int) round($order->subtotal / max(1, $order->qty)),
                    'quantity' => (int) $order->qty,
                    'name'     => mb_substr((string) $order->product_name_snapshot, 0, 50),
                ],
            ],
        ];

        if ($order->needsShipping() && (float) $order->shipping_cost > 0) {
            $base['item_details'][] = [
                'id'       => 'shipping-cost',
                'price'    => (int) round((float) $order->shipping_cost),
                'quantity' => 1,
                'name'     => 'Ongkos Kirim',
            ];
        }

        if ($order->needsShipping() && filled($order->shipping_address)) {
            $base['customer_details']['shipping_address'] = [
                'first_name'   => $order->customer_name,
                'phone'        => $order->customer_phone,
                'address'      => $order->shipping_address,
                'country_code' => 'IDN',
            ];
        }

        return match ($channel) {
            self::CHANNEL_QRIS => array_merge($base, [
                'payment_type' => 'qris',
                'qris' => [
                    'acquirer' => 'gopay',
                ],
            ]),
            self::CHANNEL_BCA_VA => array_merge($base, [
                'payment_type' => 'bank_transfer',
                'bank_transfer' => [
                    'bank' => 'bca',
                ],
            ]),
            self::CHANNEL_BNI_VA => array_merge($base, [
                'payment_type' => 'bank_transfer',
                'bank_transfer' => [
                    'bank' => 'bni',
                ],
            ]),
            self::CHANNEL_BRI_VA => array_merge($base, [
                'payment_type' => 'bank_transfer',
                'bank_transfer' => [
                    'bank' => 'bri',
                ],
            ]),
            self::CHANNEL_MANDIRI_BILL => array_merge($base, [
                'payment_type' => 'echannel',
                'echannel' => [
                    'bill_info1' => 'Pembayaran Pesanan:',
                    'bill_info2' => $order->order_number,
                ],
            ]),
            self::CHANNEL_PERMATA_VA => array_merge($base, [
                'payment_type' => 'permata',
            ]),
            self::CHANNEL_CIMB_VA => array_merge($base, [
                'payment_type' => 'bank_transfer',
                'bank_transfer' => [
                    'bank' => 'cimb',
                ],
            ]),
            self::CHANNEL_GOPAY => array_merge($base, [
                'payment_type' => 'gopay',
                'gopay' => [
                    'enable_callback' => true,
                    'callback_url'    => route('order.pending', $order->public_token),
                ],
            ]),
            self::CHANNEL_SHOPEEPAY => array_merge($base, [
                'payment_type' => 'shopeepay',
                'shopeepay' => [
                    'callback_url' => route('order.pending', $order->public_token),
                ],
            ]),
            self::CHANNEL_INDOMARET => array_merge($base, [
                'payment_type' => 'cstore',
                'cstore' => [
                    'store'   => 'indomaret',
                    'message' => 'Pesanan ' . $order->order_number,
                ],
            ]),
            self::CHANNEL_ALFAMART => array_merge($base, [
                'payment_type' => 'cstore',
                'cstore' => [
                    'store'                => 'alfamart',
                    'alfamart_free_text_1' => 'RBeverything',
                    'alfamart_free_text_2' => $order->order_number,
                ],
            ]),
            default => throw new PaymentGatewayException('Saluran tidak dikenal: ' . $channel, self::KEY),
        };
    }

    /**
     * Parse and structure the Midtrans Core API response.
     *
     * @param array<string, mixed> $res
     * @return array<string, mixed>
     */
    protected function parseChargeResponse(array $res, string $channel): array
    {
        $parsed = [
            'status_code'        => $res['status_code'] ?? null,
            'status_message'     => $res['status_message'] ?? null,
            'transaction_id'     => $res['transaction_id'] ?? null,
            'transaction_status' => $res['transaction_status'] ?? 'pending',
            'transaction_time'   => $res['transaction_time'] ?? null,
            'expiry_time'        => $res['expiry_time'] ?? null,
            'gross_amount'       => $res['gross_amount'] ?? null,
            'channel'            => $channel,
        ];

        if (in_array($channel, [self::CHANNEL_QRIS, self::CHANNEL_GOPAY, self::CHANNEL_SHOPEEPAY], true)) {
            $qrUrl = null;
            $deeplinkUrl = null;
            if (!empty($res['actions']) && is_array($res['actions'])) {
                foreach ($res['actions'] as $action) {
                    $actionName = $action['name'] ?? '';
                    if ($actionName === 'generate-qr-code') {
                        $qrUrl = $action['url'] ?? null;
                    }
                    if ($actionName === 'deeplink-redirect') {
                        $deeplinkUrl = $action['url'] ?? null;
                    }
                }
            }
            $parsed['qr_url']       = $qrUrl;
            $parsed['deeplink_url'] = $deeplinkUrl;
            $parsed['qr_string']    = $res['qr_string'] ?? null;
        } elseif (in_array($channel, [self::CHANNEL_BCA_VA, self::CHANNEL_BNI_VA, self::CHANNEL_BRI_VA, self::CHANNEL_CIMB_VA], true)) {
            $vaNumber = null;
            $bank = null;
            if (!empty($res['va_numbers']) && is_array($res['va_numbers'])) {
                $first = $res['va_numbers'][0] ?? [];
                $vaNumber = $first['va_number'] ?? null;
                $bank = $first['bank'] ?? null;
            }
            $parsed['va_number'] = $vaNumber;
            $parsed['bank']      = $bank ?? str_replace('_va', '', $channel);
        } elseif ($channel === self::CHANNEL_MANDIRI_BILL) {
            $parsed['biller_code'] = $res['biller_code'] ?? null;
            $parsed['bill_key']    = $res['bill_key'] ?? null;
        } elseif ($channel === self::CHANNEL_PERMATA_VA) {
            $parsed['va_number'] = $res['permata_va_number'] ?? ($res['va_numbers'][0]['va_number'] ?? null);
            $parsed['bank']      = 'permata';
        } elseif (in_array($channel, [self::CHANNEL_INDOMARET, self::CHANNEL_ALFAMART], true)) {
            $parsed['payment_code'] = $res['payment_code'] ?? null;
        }

        return $parsed;
    }

    /**
     * Provide step-by-step payment instructions for each payment channel.
     *
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    protected function instructionsFor(string $channel, array $payload, Order $order): array
    {
        return match ($channel) {
            self::CHANNEL_QRIS => [
                'Buka aplikasi mobile banking (BCA, Mandiri, BRI, BNI) atau e-wallet (GoPay, OVO, DANA, ShopeePay, LinkAja).',
                'Pilih menu Scan QR / QRIS.',
                'Arahkan kamera ke QR Code di atas atau upload screenshot QR code.',
                'Periksa nominal transaksi sebesar ' . Order::formatRupiah($order->payableAmount()) . ' dan selesaikan pembayaran.',
                'Pembayaran akan terverifikasi secara otomatis dalam beberapa detik.',
            ],
            self::CHANNEL_BCA_VA => [
                'Buka aplikasi BCA Mobile / KlikBCA / ATM BCA.',
                'Pilih menu Transfer > BCA Virtual Account.',
                'Masukkan Nomor Virtual Account: ' . ($payload['va_number'] ?? '-'),
                'Pastikan nama penerima dan tagihan sebesar ' . Order::formatRupiah($order->payableAmount()) . ' sesuai.',
                'Konfirmasi transaksi dan masukkan PIN Anda.',
            ],
            self::CHANNEL_BNI_VA => [
                'Buka aplikasi BNI Mobile Banking atau kunjungi ATM BNI.',
                'Pilih menu Pembayaran / Transfer > Virtual Account Billing.',
                'Masukkan Nomor Virtual Account: ' . ($payload['va_number'] ?? '-'),
                'Periksa rincian pembayaran dan masukkan PIN transaksi.',
            ],
            self::CHANNEL_BRI_VA => [
                'Buka aplikasi BRImo atau kunjungi ATM BRI.',
                'Pilih menu Pembayaran > BRIVA.',
                'Masukkan Nomor Virtual Account BRIVA: ' . ($payload['va_number'] ?? '-'),
                'Konfirmasi pembayaran dan masukkan PIN transaksi Anda.',
            ],
            self::CHANNEL_MANDIRI_BILL => [
                'Buka aplikasi Livin by Mandiri atau ATM Mandiri.',
                'Pilih menu Bayar / Pembayaran > Cari Penyedia Jasa / Biller Code: ' . ($payload['biller_code'] ?? '70012'),
                'Masukkan Nomor Tagihan / Bill Key: ' . ($payload['bill_key'] ?? '-'),
                'Periksa detail tagihan ' . Order::formatRupiah($order->payableAmount()) . ' dan konfirmasi pembayaran.',
            ],
            self::CHANNEL_PERMATA_VA => [
                'Buka aplikasi PermataMobile X atau ATM Bank Permata / ATM Bersama.',
                'Pilih menu Transfer > Ke Rekening Virtual Account.',
                'Masukkan Nomor Virtual Account Permata: ' . ($payload['va_number'] ?? '-'),
                'Periksa rincian pembayaran dan konfirmasi transaksi.',
            ],
            self::CHANNEL_CIMB_VA => [
                'Buka aplikasi OCTO Mobile, OCTO Clicks, atau kunjungi ATM CIMB Niaga / ATM Bersama.',
                'Pilih menu Transfer > Rekening Virtual Account CIMB Niaga.',
                'Masukkan Nomor Virtual Account: ' . ($payload['va_number'] ?? '-'),
                'Pastikan nominal ' . Order::formatRupiah($order->payableAmount()) . ' sesuai dan selesaikan pembayaran.',
            ],
            self::CHANNEL_GOPAY => [
                'Jika Anda menggunakan smartphone, klik tombol Buka Aplikasi Gojek di atas.',
                'Jika Anda menggunakan laptop/PC, buka aplikasi Gojek di ponsel Anda lalu scan QR Code di layar.',
                'Periksa rincian pembayaran ' . Order::formatRupiah($order->payableAmount()) . ' dan masukkan PIN GoPay Anda.',
            ],
            self::CHANNEL_SHOPEEPAY => [
                'Jika Anda menggunakan smartphone, klik tombol Buka Aplikasi Shopee di atas.',
                'Jika Anda menggunakan laptop/PC, buka aplikasi Shopee di ponsel Anda lalu scan QR Code di layar.',
                'Periksa rincian pembayaran ' . Order::formatRupiah($order->payableAmount()) . ' dan masukkan PIN ShopeePay Anda.',
            ],
            self::CHANNEL_INDOMARET => [
                'Kunjungi gerai Indomaret atau Ceriamart terdekat.',
                'Sampaikan kepada kasir bahwa Anda ingin melakukan pembayaran merchant RBeverything.',
                'Tunjukkan Kode Pembayaran: ' . ($payload['payment_code'] ?? '-'),
                'Bayar sesuai tagihan kasir sebesar ' . Order::formatRupiah($order->payableAmount()) . ' dan simpan struk pembayaran.',
            ],
            self::CHANNEL_ALFAMART => [
                'Kunjungi gerai Alfamart, Alfamidi, Lawson, atau Dan+Dan terdekat.',
                'Sampaikan kepada kasir bahwa Anda ingin melakukan pembayaran transaksi merchant RBeverything.',
                'Tunjukkan Kode Pembayaran: ' . ($payload['payment_code'] ?? '-'),
                'Bayar sesuai tagihan kasir sebesar ' . Order::formatRupiah($order->payableAmount()) . ' dan simpan struk pembayaran.',
            ],
            default => [
                'Selesaikan pembayaran sesuai instruksi pada metode yang dipilih.',
                'Status pesanan akan diperbarui secara otomatis begitu dana diterima.',
            ],
        };
    }

    /**
     * Apply SDK global configuration.
     */
    public function applyConfig(): void
    {
        MidtransConfig::$serverKey    = $this->getServerKey();
        MidtransConfig::$clientKey    = $this->getClientKey();
        MidtransConfig::$isProduction = $this->isProduction();
        MidtransConfig::$isSanitized  = true;
        MidtransConfig::$is3ds        = true;
    }
}
