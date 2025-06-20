<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeliverySlot extends Model
{
    protected $fillable = [
        'date', 
        'start_time', 
        'end_time', 
        'price', 
        'available_slots'
    ];

    protected $casts = [
        'date' => 'date',
        'price' => 'decimal:2',
        'available_slots' => 'integer'
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    // Check if slot is available
    public function isAvailable(): bool
    {
        return $this->date >= today() && $this->available_slots > 0;
    }

    // Get current available slots (accounting for existing orders)
    public function getCurrentAvailableSlots(): int
    {
        $bookedSlots = $this->orders()->where('status', '!=', 'cancelled')->count();
        return max(0, $this->available_slots - $bookedSlots);
    }

    // Scope for available slots
    public function scopeAvailable($query)
    {
        return $query->where('date', '>=', now()->startOfDay())
            ->where('available_slots', '>', 0)
            ->orderBy('date')
            ->orderBy('start_time');
    }
}