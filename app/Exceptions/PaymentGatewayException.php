<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * A payment provider could not be reached, or refused what we asked it.
 *
 * Its whole job is to be catchable: OrderController turns this into a plain
 * sentence on the payment page ("pembayaran online sedang tidak tersedia")
 * instead of letting a bad API key or a network timeout surface as a raw 500
 * to someone who is trying to give us money.
 */
class PaymentGatewayException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly string $gateway = 'unknown',
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }

    /** What the buyer is shown. Never the provider's raw error text. */
    public function userMessage(): string
    {
        return 'Pembayaran online sedang tidak tersedia. Silakan hubungi kami untuk menyelesaikan pesanan ini.';
    }
}
