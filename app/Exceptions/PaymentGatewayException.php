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

    /** What the buyer is shown. Displays specific gateway messages when available. */
    public function userMessage(): string
    {
        if (filled($this->customUserMessage)) {
            return $this->customUserMessage;
        }

        if ($this->gateway === 'paylabs' && filled($this->getMessage())) {
            return $this->getMessage();
        }

        return 'Pembayaran online sedang tidak tersedia. Silakan hubungi kami untuk menyelesaikan pesanan ini.';
    }
}
