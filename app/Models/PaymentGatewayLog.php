<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;

class PaymentGatewayLog extends Model
{
    protected $fillable = [
        'order_id',
        'gateway',
        'direction',
        'action',
        'method',
        'endpoint',
        'transaction_id',
        'amount',
        'response_status',
        'status',
        'error_message',
        'request_headers',
        'request_payload',
        'response_headers',
        'response_payload',
        'ip_address',
    ];

    protected $casts = [
        'amount'           => 'decimal:2',
        'response_status'  => 'integer',
        'request_headers'  => 'array',
        'request_payload'  => 'array',
        'response_headers' => 'array',
        'response_payload' => 'array',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id', 'order_number');
    }

    /**
     * Safely record an API transaction log without interrupting order flow.
     */
    public static function record(array $attributes): ?self
    {
        try {
            return static::create($attributes);
        } catch (\Throwable $e) {
            Log::warning('Gagal menyimpan PaymentGatewayLog: ' . $e->getMessage(), [
                'order_id'  => $attributes['order_id'] ?? null,
                'gateway'   => $attributes['gateway'] ?? 'paylabs',
                'direction' => $attributes['direction'] ?? 'outgoing',
                'action'    => $attributes['action'] ?? null,
            ]);

            return null;
        }
    }

    public function formattedAmount(): string
    {
        if ($this->amount === null) {
            return '—';
        }

        return Order::formatRupiah((float) $this->amount);
    }

    public function directionLabel(): string
    {
        return $this->direction === 'incoming'
            ? 'Masuk (Webhook Notifikasi)'
            : 'Keluar (API Request)';
    }

    public function directionColor(): string
    {
        return $this->direction === 'incoming' ? 'success' : 'info';
    }

    public function directionIcon(): string
    {
        return $this->direction === 'incoming'
            ? 'heroicon-m-arrow-down-left'
            : 'heroicon-m-arrow-up-right';
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            'success'           => 'success',
            'failed', 'error'   => 'danger',
            'invalid_signature' => 'danger',
            'pending'           => 'warning',
            default             => 'gray',
        };
    }

    public function actionLabel(): string
    {
        return match ($this->action) {
            'create_qris'          => 'Generate QRIS',
            'create_va'            => 'Generate Virtual Account',
            'query_status'         => 'Cek Status Transaksi',
            'cancel_qris'          => 'Batalkan QRIS',
            'webhook_notification' => 'Webhook Notifikasi',
            default                => ucwords(str_replace('_', ' ', $this->action ?? 'Action')),
        };
    }

    /**
     * Generate structured evidence text ready to share with payment gateway support / merchant ops.
     */
    public function generateEvidenceText(): string
    {
        $data = [
            'timestamp'       => $this->created_at?->toIso8601String() ?? date('c'),
            'gateway'         => strtoupper($this->gateway),
            'direction'       => $this->direction,
            'action'          => $this->action,
            'order_id'        => $this->order_id,
            'transaction_id'  => $this->transaction_id,
            'endpoint'        => $this->endpoint,
            'method'          => $this->method,
            'http_status'     => $this->response_status,
            'status'          => $this->status,
            'error_message'   => $this->error_message,
            'request_headers' => $this->request_headers,
            'request_payload' => $this->request_payload,
            'response_payload'=> $this->response_payload,
        ];

        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}
