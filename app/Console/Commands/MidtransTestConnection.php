<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Midtrans\Config as MidtransConfig;
use Midtrans\Transaction;

/**
 * "Is the Midtrans container ready to plug in?"
 *
 * Answers that from the terminal without creating a real order: it asks
 * Midtrans about a transaction id that cannot exist. A 404 back is the good
 * outcome — the key was accepted and only the transaction was missing. A 401
 * means the key itself was refused.
 */
class MidtransTestConnection extends Command
{
    protected $signature = 'midtrans:test-connection';

    protected $description = 'Check whether the configured Midtrans server key is accepted';

    public function handle(): int
    {
        $serverKey    = (string) config('services.midtrans.server_key');
        $isProduction = (bool) config('services.midtrans.is_production', false);
        $isActive     = (bool) config('services.midtrans.is_active', false);

        $this->line('Environment : ' . ($isProduction ? 'production' : 'sandbox'));
        $this->line('Server key  : ' . ($serverKey === '' ? '(kosong)' : $this->maskKey($serverKey)));
        $this->line('Aktif       : ' . ($isActive ? 'ya' : 'tidak (MIDTRANS_IS_ACTIVE=false)'));
        $this->newLine();

        if ($serverKey === '') {
            $this->error('MIDTRANS_SERVER_KEY belum diisi — tidak ada yang bisa diuji.');

            return self::FAILURE;
        }

        MidtransConfig::$serverKey    = $serverKey;
        MidtransConfig::$isProduction = $isProduction;

        // An id no real transaction will ever have, so this can be run against
        // production without touching anything.
        $probeId = 'rb-connection-test-' . now()->format('YmdHis');

        try {
            Transaction::status($probeId);

            // Astonishing, but harmless: the key works.
            $this->info('✅ Koneksi berhasil — server key diterima Midtrans.');

            return self::SUCCESS;
        } catch (\Throwable $e) {
            return $this->interpret($e);
        }
    }

    protected function interpret(\Throwable $e): int
    {
        $code = (int) $e->getCode();

        if ($code === 404) {
            // What we expect: authenticated fine, transaction simply not there.
            $this->info('✅ Koneksi berhasil — server key diterima Midtrans (transaksi uji memang tidak ada).');

            if (! config('services.midtrans.is_active', false)) {
                $this->newLine();
                $this->warn('Midtrans masih non-aktif. Set MIDTRANS_IS_ACTIVE=true untuk mulai memakainya.');
                $this->line('Langkah lengkap: docs/MIDTRANS_ACTIVATION.md');
            }

            return self::SUCCESS;
        }

        if ($code === 401) {
            $this->error('❌ Server key ditolak Midtrans (401). Periksa apakah key-nya benar dan cocok dengan environment (sandbox vs production).');

            return self::FAILURE;
        }

        $this->error('❌ Gagal menghubungi Midtrans: ' . $e->getMessage());

        return self::FAILURE;
    }

    /** Never print a whole server key to a terminal someone might screenshot. */
    protected function maskKey(string $key): string
    {
        return mb_substr($key, 0, 8) . str_repeat('*', max(0, mb_strlen($key) - 8));
    }
}
