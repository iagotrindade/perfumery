<?php

namespace App\Models;

use App\Models\ProductSale;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = [
        'customer_id',
        'due_date',
        'total'
    ];

    protected $casts = [
        // transforma o campo JSON em array automaticamente
        'due_date' => 'date',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function products()
    {
        return $this->hasMany(ProductSale::class, 'sale_id', 'id');
    }

    public function installments()
    {
        return $this->hasMany(SaleInstallment::class, 'sale_id', 'id');
    }
}
