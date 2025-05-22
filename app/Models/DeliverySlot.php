<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliverySlot extends Model
{
    protected $fillable = ['date', 'start_time', 'end_time', 'price'];

    protected $casts = [
        'date' => 'date',
        'price' => 'decimal:2'
    ];
}
