<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentNotificationLog extends Model
{
    protected $fillable = [
        'order_id',
        'transaction_id',
        'payment_type',
        'transaction_status',
        'fraud_status',
        'status_code',
        'gross_amount',
        'signature_key',
        'is_valid_signature',
        'is_test_notification',
        'response_status',
        'response_message',
        'payload',
        'ip_address',
    ];

    protected $casts = [
        'gross_amount'         => 'decimal:2',
        'is_valid_signature'   => 'boolean',
        'is_test_notification' => 'boolean',
        'response_status'      => 'integer',
        'payload'              => 'array',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id', 'order_number');
    }

    public function formattedAmount(): string
    {
        if ($this->gross_amount === null) {
            return '—';
        }

        return Order::formatRupiah($this->gross_amount);
    }

    public function statusColor(): string
    {
        return match ($this->transaction_status) {
            'settlement', 'capture' => 'success',
            'pending'               => 'warning',
            'deny', 'cancel'        => 'danger',
            'expire'                => 'gray',
            default                 => 'info',
        };
    }
}
