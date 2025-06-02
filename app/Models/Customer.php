<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Address;

class Customer extends Model
{
    protected $fillable = [
        'name',
        'cpf',
        'email',
        'phone',
        'description'
    ];

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }
}
