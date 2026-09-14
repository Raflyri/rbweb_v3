<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'product_name_snapshot',
        'product_type_snapshot',
        'price_snapshot',
        'qty',
        'subtotal',
    ];

    protected $casts = [
        'price_snapshot' => 'decimal:2',
        'subtotal'       => 'decimal:2',
        'qty'            => 'integer',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function formattedPrice(): string
    {
        return Order::formatRupiah($this->price_snapshot);
    }

    public function formattedSubtotal(): string
    {
        return Order::formatRupiah($this->subtotal);
    }
}
