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
        'postal_code',
        'city',
        'country',
    ];

    /**
     * Get the user associated with the address.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
