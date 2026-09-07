<?php

namespace App\Services\Payment;

use App\Models\Order;
use App\Notifications\PaymentStatusUpdated;
use App\Support\PaymentStatus;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

/**
 * The two decisions an admin makes about a manual payment.
 *
 * They live here rather than inside the Filament actions so the rules can be
 * tested directly — and so a future "confirm from somewhere else" has one
 * implementation to call instead of a copy.
 */
class PaymentActions
{
    /** Disk holding uploaded receipts. Private: see OrderController::proof(). */
    public const PROOF_DISK = 'local';

    public const PROOF_DIRECTORY = 'payment-proofs';

    /**
     * Mark an order paid.
     *
     * paid_at is only set if it is still empty, so re-confirming an order does
     * not quietly move the date the money actually arrived.
     */
    public function confirm(Order $order, ?string $method = null): void
    {
        $order->forceFill([
            'payment_status' => PaymentStatus::LUNAS,
            'payment_method' => $order->payment_method ?: ($method ?: ManualTransferGateway::KEY),
            'paid_at'        => $order->paid_at ?? now(),
        ])->save();

        $this->notifyCustomer($order, PaymentStatusUpdated::CONFIRMED);
    }

    /**
     * Send a receipt back for another try.
     *
     * The file is deliberately kept: it is the evidence of what was rejected,
     * and the buyer's next upload replaces it anyway.
     */
    public function rejectProof(Order $order, string $reason): void
    {
        $order->forceFill([
            'payment_status' => PaymentStatus::MENUNGGU,
            'payment_note'   => $reason,
        ])->save();

        $this->notifyCustomer($order, PaymentStatusUpdated::REJECTED, $reason);
    }

    /**
     * Store a buyer's receipt and put the order in the verification queue.
     *
     * Any previous file is deleted — a rejected receipt has served its purpose
     * once a replacement arrives, and keeping every attempt would grow the
     * disk for no one's benefit.
     */
    public function attachProof(Order $order, string $path, string $method = ManualTransferGateway::KEY): void
    {
        $previous = $order->payment_proof;

        $order->forceFill([
            'payment_proof'  => $path,
            'payment_status' => PaymentStatus::MENUNGGU_VERIFIKASI,
            'payment_method' => $method,
            // The buyer has acted on the previous rejection; leaving the old
            // note up would keep telling them off for a fixed problem.
            'payment_note'   => null,
        ])->save();

        if ($previous && $previous !== $path) {
            Storage::disk(self::PROOF_DISK)->delete($previous);
        }
    }

    /**
     * Email the buyer without letting the mail server undo the decision.
     *
     * The status change is already committed by the time this runs; a dead
     * SMTP host must not roll it back or 500 the admin panel.
     */
    protected function notifyCustomer(Order $order, string $outcome, ?string $reason = null): void
    {
        try {
            Notification::route('mail', $order->customer_email)
                ->notify(new PaymentStatusUpdated($order, $outcome, $reason));
        } catch (\Throwable $e) {
            Log::error('Payment status email failed to send', [
                'order_number' => $order->order_number,
                'outcome'      => $outcome,
                'error'        => $e->getMessage(),
            ]);
        }
    }
}
