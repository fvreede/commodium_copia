<?php

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