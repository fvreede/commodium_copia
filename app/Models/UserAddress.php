<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserAddress extends Model
{
    protected $fillable = [
        'user_id',
        'street',
        'house_number',
        'city',
        'postal_code',
        'country'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get formatted address string
     */
    public function getFormattedAddressAttribute(): string
    {
        $address = $this->street;
        
        if ($this->house_number) {
            $address .= ' ' . $this->house_number;
        }
        
        $address .= ', ' . $this->postal_code . ' ' . $this->city;
        
        if ($this->country && $this->country !== 'Netherlands') {
            $address .= ', ' . $this->country;
        }
        
        return $address;
    }
}