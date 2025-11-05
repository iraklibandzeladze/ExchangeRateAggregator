<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExchangeRate extends Model
{
    protected $fillable = [
        'country',
        'currency_code',
        'rate',
        'date'
    ];
    
    protected $casts = ['date' => 'date'];
}
