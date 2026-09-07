<?php

namespace App\Services\Payment;

use App\Contracts\PaymentGateway;
use App\Models\Order;

/**
 * Bank transfer, verified by hand.
 *
 * No API, no keys, no webhook: the buyer transfers, uploads a receipt, and an
 * admin confirms it. That is the whole method — and it works today, which is
 * the point while the Midtrans account is still being sorted out.
 */
class ManualTransferGateway implements PaymentGateway
{
    public const KEY = 'manual_transfer';

    /**
     * Placeholder values shipped in config/services.php. If the config still
     * matches one of these, nobody has filled the real account in yet.
     */
    protected const PLACEHOLDERS = ['ISI_NAMA_BANK', 'ISI_NOMOR_REKENING', 'ISI_ATAS_NAMA'];

    public function key(): string
    {
        return self::KEY;
    }

    public function name(): string
    {
        return 'Transfer Bank Manual';
    }

    /**
     * Has a real account been configured?
     *
     * Checked before any of this reaches a buyer: printing "ISI_NOMOR_REKENING"
     * on a checkout page is worse than saying plainly that instructions will
     * follow, so the view falls back to the contact buttons instead.
     */
    public function isConfigured(): bool
    {
        foreach ($this->account() as $value) {
            if (trim((string) $value) === '' || in_array($value, self::PLACEHOLDERS, true)) {
                return false;
            }
        }

        return true;
    }

    /** @return array{bank_name: string, account_number: string, account_holder: string} */
    public function account(): array
    {
        $config = config('services.manual_transfer', []);

        return [
            'bank_name'      => (string) ($config['bank_name'] ?? ''),
            'account_number' => (string) ($config['account_number'] ?? ''),
            'account_holder' => (string) ($config['account_holder'] ?? ''),
        ];
    }

    /** @return array<string, mixed> */
    public function charge(Order $order): array
    {
        $amount = $order->payableAmount();

        return [
            'type'             => self::KEY,
            'gateway'          => self::KEY,
            'name'             => $this->name(),
            'configured'       => $this->isConfigured(),
            'amount'           => $amount,
            'formatted_amount' => Order::formatRupiah($amount),
            // What the buyer must write on the transfer so it can be matched
            // back to their order without a phone call.
            'reference'        => $order->order_number,
            'account'          => $this->account(),
            'instructions'     => [
                'Transfer tepat sebesar ' . Order::formatRupiah($amount) . ' ke rekening di atas.',
                'Cantumkan ' . $order->order_number . ' pada berita/keterangan transfer.',
                'Unggah bukti transfer lewat formulir di bawah, lalu tunggu konfirmasi dari kami.',
            ],
        ];
    }
}
