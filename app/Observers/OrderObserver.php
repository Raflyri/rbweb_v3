<?php

namespace App\Observers;

use App\Models\Order;

class OrderObserver
{
    /**
     * Handle the Order "created" event.
     */
    public function created(Order $order): void
    {
        //
    }

    public function updated(Order $order): void
    {
        if ($order->wasChanged('status')) {
            $newStatus = $order->status;
            
            // Notification only for DIPROSES, DIKIRIM, and SELESAI
            if (in_array($newStatus, [\App\Support\OrderStatus::DIPROSES, \App\Support\OrderStatus::DIKIRIM, \App\Support\OrderStatus::SELESAI])) {
                try {
                    \Illuminate\Support\Facades\Notification::route('mail', $order->customer_email)
                        ->notify(new \App\Notifications\OrderStatusUpdated($order));
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::error('Order status email failed to send', [
                        'order_number' => $order->order_number,
                        'status'       => $newStatus,
                        'error'        => $e->getMessage(),
                    ]);
                    
                    \App\Models\EmailLog::create([
                        'to_email' => $order->customer_email,
                        'subject'  => "Pembaruan Status Pesanan: {$order->order_number}",
                        'status'   => 'failed',
                        'error_message' => $e->getMessage(),
                    ]);
                }
            }
        }
    }

    /**
     * Handle the Order "deleted" event.
     */
    public function deleted(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "restored" event.
     */
    public function restored(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "force deleted" event.
     */
    public function forceDeleted(Order $order): void
    {
        //
    }
}
