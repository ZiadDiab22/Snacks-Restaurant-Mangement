<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class order extends Model
{
    use HasFactory;

    protected $fillable = [
        'status_id',
        'user_id',
        'delivery_emp_id',
        'emp_id',
        'sector_id',
        'lat',
        'lng',
        'distance',
        'delivery_price',
        'order_price',
        'total_price'
    ];
}
