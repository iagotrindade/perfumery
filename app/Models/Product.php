<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'image',
        'name',
        'category_id',
        'brand_id',
        'quantity',
        'cost_value',
        'sale_value',
        'description',
        'show_on_catalog',
    ];

    protected $casts = [
        'image' => 'array',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function sales()
    {
        return $this->belongsToMany(Sale::class, 'product_sales')
            ->withPivot('quantity')
            ->withTimestamps();
    }
}
