<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'image',
        'name',
        'quantity',
        'cost_value',
        'sale_value',
        'description'
    ];

    protected $casts = [
        'image' => 'array',
    ];

    public function sales()
    {
        return $this->belongsToMany(Sale::class)
            ->withPivot('quantity')
            ->withTimestamps();
    }
}
