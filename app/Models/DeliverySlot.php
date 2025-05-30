<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeliverySlot extends Model
{
    protected $fillable = ['date', 'start_time', 'end_time', 'price'];

    protected $casts = [
        'date' => 'date',
        'price' => 'decimal:2'
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    // Check if slot is available
    public function isAvailable(): bool
    {
        // You can add logic here to check capacity, etc.
        return $this->date >= today();
    }
}

// Enhanced Order Model
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'user_id', 
        'delivery_slot_id', 
        'status', 
        'total',
        'delivery_address',
        'notes'
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'delivery_address' => 'array'
    ];

    // Order statuses
    const STATUS_PENDING = 'pending';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_PROCESSING = 'processing';
    const STATUS_OUT_FOR_DELIVERY = 'out_for_delivery';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_CANCELLED = 'cancelled';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function deliverySlot(): BelongsTo
    {
        return $this->belongsTo(DeliverySlot::class);
    }

    // Calculate total from items
    public function calculateTotal(): float
    {
        return $this->items->sum(function ($item) {
            return $item->quantity * $item->price;
        });
    }

    // Scope for active orders
    public function scopeActive($query)
    {
        return $query->whereIn('status', [
            self::STATUS_PENDING,
            self::STATUS_CONFIRMED,
            self::STATUS_PROCESSING,
            self::STATUS_OUT_FOR_DELIVERY
        ]);
    }
}

// Enhanced OrderItem Model
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id', 
        'product_id', 
        'quantity', 
        'price',
        'product_name' // Store name in case product is deleted
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'quantity' => 'integer'
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // Calculate line total
    public function getLineTotalAttribute(): float
    {
        return $this->quantity * $this->price;
    }
}

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