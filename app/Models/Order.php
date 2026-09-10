<?php

namespace App\Models;

use App\Support\OrderStatus;
use App\Support\PaymentStatus;
use App\Support\ProductType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * One order for one product — deliberately not a cart.
 *
 * At this scale a buyer picks a single item and fills in a form; multi-item
 * carts, coupons and stock reservations are all complexity nobody has asked
 * for yet. The product is referenced *and* snapshotted: the relation keeps the
 * admin's link to the live listing, the snapshot keeps the order honest when
 * that listing is later renamed, repriced, or deleted.
 */
class Order extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'product_id',
        'product_name_snapshot',
        'product_type_snapshot',
        'price_snapshot',
        'qty',
        'subtotal',
        'shipping_cost',
        'total',
        'customer_name',
        'customer_email',
        'customer_phone',
        'shipping_address',
        'preferred_date',
        'notes',
        'status',
        'payment_status',
        'payment_method',
        'payment_proof',
        'payment_note',
        'paid_at',
    ];

    protected $casts = [
        'price_snapshot' => 'decimal:2',
        'subtotal'       => 'decimal:2',
        'shipping_cost'  => 'decimal:2',
        'total'          => 'decimal:2',
        'qty'            => 'integer',
        'preferred_date' => 'date',
        'paid_at'        => 'datetime',
    ];

    /**
     * order_number and public_token are assigned here, never by the caller, so
     * there is no code path that can create an order without them.
     */
    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            $order->order_number ??= static::generateOrderNumber();
            $order->public_token ??= static::generatePublicToken();
        });
    }

    // ── Creation ─────────────────────────────────────────────────────────
    /**
     * Create an order, surviving the race two simultaneous buyers create.
     *
     * generateOrderNumber() reads the highest number used today and adds one,
     * so two requests landing together compute the same value and the second
     * insert dies on the unique index. Rather than lock the table on every
     * order — the collision is rare and the lock would be held across the whole
     * insert — the duplicate is caught and the number recomputed. The retry is
     * safe because nothing has been written when it fires.
     */
    public static function place(array $attributes): self
    {
        $lastFailure = null;

        for ($attempt = 1; $attempt <= 5; $attempt++) {
            try {
                return static::create($attributes);
            } catch (UniqueConstraintViolationException $e) {
                // A fresh model is built on the next pass, so creating() runs
                // again and draws a new number from the now-updated table.
                $lastFailure = $e;
            }
        }

        throw $lastFailure;
    }

    // ── Identity ─────────────────────────────────────────────────────────
    /** RB-YYYYMMDD-NNNN, counting from 1 again each day. */
    public static function generateOrderNumber(?\DateTimeInterface $on = null): string
    {
        $date   = ($on ? \Illuminate\Support\Carbon::instance($on) : now())->format('Ymd');
        $prefix = "RB-{$date}-";

        // Read the highest sequence already used today rather than counting
        // rows: a deleted order must not hand its number to the next one.
        $latest = static::withoutGlobalScopes()
            ->where('order_number', 'like', $prefix . '%')
            ->orderByDesc('order_number')
            ->value('order_number');

        $next = $latest ? ((int) Str::afterLast($latest, '-')) + 1 : 1;

        return $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    public static function generatePublicToken(): string
    {
        return Str::lower(Str::random(40));
    }

    /** The public order page is keyed by the token, never by the order number. */
    public function getRouteKeyName(): string
    {
        return 'public_token';
    }

    // ── Relationships ────────────────────────────────────────────────────
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // ── Scopes ───────────────────────────────────────────────────────────
    /** Named for the status, not "new" — that reads as newInstance() here. */
    public function scopeBaru(Builder $query): Builder
    {
        return $query->where('status', OrderStatus::BARU);
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereIn('status', [OrderStatus::BARU, OrderStatus::DIPROSES]);
    }

    public function scopeAwaitingPayment(Builder $query): Builder
    {
        return $query->whereIn('payment_status', [
            PaymentStatus::MENUNGGU,
            PaymentStatus::MENUNGGU_VERIFIKASI,
        ]);
    }

    public function scopePaid(Builder $query): Builder
    {
        return $query->where('payment_status', PaymentStatus::LUNAS);
    }

    // ── Money ────────────────────────────────────────────────────────────
    /**
     * What the buyer owes right now.
     *
     * Falls back to the subtotal while shipping has not been quoted yet, so
     * every screen can print one number without special-casing.
     */
    public function payableAmount(): float
    {
        return (float) ($this->total ?? $this->subtotal);
    }

    public function formattedSubtotal(): string
    {
        return static::formatRupiah($this->subtotal);
    }

    public function formattedShipping(): ?string
    {
        return $this->shipping_cost === null ? null : static::formatRupiah($this->shipping_cost);
    }

    public function formattedTotal(): string
    {
        return static::formatRupiah($this->payableAmount());
    }

    public static function formatRupiah(int|float|string|null $amount): string
    {
        $value = (float) $amount;

        return 'Rp ' . number_format($value, fmod($value, 1.0) !== 0.0 ? 2 : 0, ',', '.');
    }

    /**
     * Recalculate total from whatever is currently known.
     *
     * Kept explicit rather than an accessor: the stored total is what an admin
     * may have overridden by hand, and silently recomputing it on read would
     * throw that away.
     */
    public function recalculateTotal(): void
    {
        $this->total = (float) $this->subtotal + (float) ($this->shipping_cost ?? 0);
    }

    // ── State helpers ────────────────────────────────────────────────────
    public function needsShipping(): bool
    {
        return $this->product_type_snapshot === ProductType::BARANG;
    }

    public function isPaid(): bool
    {
        return $this->payment_status === PaymentStatus::LUNAS;
    }

    public function isCancelled(): bool
    {
        return $this->status === OrderStatus::DIBATALKAN;
    }

    // ── Activity Log Config ──────────────────────────────────────────────
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('order');
    }
}
