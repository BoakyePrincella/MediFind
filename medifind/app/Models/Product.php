<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'description',
        'brand', 'image', 'category_id', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public static function slugFromName(string $name): string
    {
        return Str::slug($name);
    }

    public static function slugExists(string $slug, ?int $ignoreId = null): bool
    {
        return static::query()
            ->where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->exists();
    }

    // ─── Relationships ───────────────────────────────

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    // All shop_products entries for this product
    public function shopProducts(): HasMany
    {
        return $this->hasMany(ShopProduct::class);
    }

    // All shops that stock this product (via shop_products)
    public function shops()
    {
        return $this->belongsToMany(Shop::class, 'shop_products')
                    ->withPivot('price', 'in_stock', 'notes')
                    ->withTimestamps();
    }

    // ─── Scopes ──────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('brand', 'like', "%{$term}%")
              ->orWhere('description', 'like', "%{$term}%");
        });
    }
}
