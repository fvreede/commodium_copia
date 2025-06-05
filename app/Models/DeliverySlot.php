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