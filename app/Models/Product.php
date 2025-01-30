<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
        'image_path'
    ];

    public function subcategory()
    {
        return $this->belongsTo(Subcategory::class);
    }

    public function promotions(): BelongsToMany
    {
        return $this->belongsToMany(Promotion::class, 'promotion_products')->withPivot('discount_price')->withTimestamps();
    }
}
