<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class product extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $fillable = [
        'name', 'type_id', 'disc', 'price', 'quantity',
        'discount_rate', 'img_url', 'visible', 'likes'
    ];
}
