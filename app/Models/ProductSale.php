<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductSale extends Model
{
    protected $fillable = [
        'product_id',
        'sale_id',
        'quantity',
    ];

    public function sales()
    {
        return $this->belongsTo(Sale::class, 'sale_id', 'id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }
}
