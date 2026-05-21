<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    // No timestamps on this model — order has them
    public $timestamps = false;

    protected $fillable = [
        'order_id', 'shop_product_id',
        'quantity', 'unit_price',
    ];

    protected $casts = [
        'unit_price' => 'float',
        'quantity'   => 'integer',
    ];

    // ─── Relationships ───────────────────────────────

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function shopProduct(): BelongsTo
    {
        return $this->belongsTo(ShopProduct::class);
    }

    // Convenience: total for this line item
    public function getSubtotalAttribute(): float
    {
        return $this->quantity * $this->unit_price;
    }
}
