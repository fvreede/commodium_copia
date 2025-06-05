<?php

// Enhanced Product Model
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'subcategory_id',
        'name',
        'short_description',
        'full_description',
        'price',
        'image_path',
        'stock_quantity',
        'is_active'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'stock_quantity' => 'integer'
    ];

    public function subcategory()
    {
        return $this->belongsTo(Subcategory::class);
    }

    public function promotions(): BelongsToMany
    {
        return $this->belongsToMany(Promotion::class, 'promotion_products')
            ->withPivot('discount_price')
            ->withTimestamps();
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    // Check if product is in stock
    public function isInStock($quantity = 1): bool
    {
        return $this->is_active && $this->stock_quantity >= $quantity;
    }

    // Get current price (with promotion if applicable)
    public function getCurrentPrice(): float
    {
        $activePromotion = $this->promotions()
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->first();

        return $activePromotion ? $activePromotion->pivot->discount_price : $this->price;
    }
}