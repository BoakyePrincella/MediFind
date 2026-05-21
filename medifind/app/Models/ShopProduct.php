<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};

class ShopProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'shop_id', 
        'product_id',
        'price', 
        'in_stock', 
        'notes',
    ];

    protected $casts = [
        'price'    => 'float',
        'in_stock' => 'boolean',
    ];

    // ─── Relationships ───────────────────────────────

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    // ─── Scopes ──────────────────────────────────────

    public function scopeInStock($query)
    {
        return $query->where('in_stock', true);
    }
}
