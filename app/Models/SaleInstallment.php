<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleInstallment extends Model
{
    protected $fillable = [
        'sale_id',
        'installment_number',
        'amount',
        'payment_date',
        'due_date',
        'status',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }
}
