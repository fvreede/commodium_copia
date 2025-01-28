<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Promotion extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'cta_text',
        'image_path',
        'is_active',
        'valid_until'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'valid_until' => 'datetime'
    ];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'promotion_products')->withPivot('discount_price')->withTimestamps();
    }
}
