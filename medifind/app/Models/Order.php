<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'shop_id', 'type',
        'status', 'delivery_address',
        'total_amount', 'notes',
    ];

    protected $casts = [
        'total_amount' => 'float',
    ];

    // ─── Relationships ───────────────────────────────

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    // ─── Status helpers ──────────────────────────────

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isDelivery(): bool
    {
        return $this->type === 'delivery';
    }
}

