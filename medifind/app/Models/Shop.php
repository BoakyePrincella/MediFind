<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};

class Shop extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'name', 'slug', 'description',
        'phone', 'address', 'city',
        'latitude', 'longitude', 'logo',
        'is_verified', 'is_active',
        'offers_delivery', 'delivery_radius_km',
    ];

    protected $casts = [
        'latitude'          => 'float',
        'longitude'         => 'float',
        'is_verified'       => 'boolean',
        'is_active'         => 'boolean',
        'offers_delivery'   => 'boolean',
        'delivery_radius_km'=> 'float',
    ];

    // ─── Relationships ───────────────────────────────

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function shopProducts(): HasMany
    {
        return $this->hasMany(ShopProduct::class);
    }

    // All products this shop stocks (via shop_products)
    public function products()
    {
        return $this->belongsToMany(Product::class, 'shop_products')
                    ->withPivot('price', 'in_stock', 'notes')
                    ->withTimestamps();
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    // ─── Scopes ──────────────────────────────────────

    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInCity($query, string $city)
    {
        return $query->where('city', $city);
    }

    // Filter shops within X km of a coordinate
    public function scopeNearby($query, float $lat, float $lng, float $km = 5)
    {
        return $query->selectRaw('*,
            ( 6371 * acos(
                cos(radians(?)) * cos(radians(latitude))
                * cos(radians(longitude) - radians(?))
                + sin(radians(?)) * sin(radians(latitude))
            )) AS distance', [$lat, $lng, $lat])
        ->having('distance', '<=', $km)
        ->orderBy('distance');
    }
}
