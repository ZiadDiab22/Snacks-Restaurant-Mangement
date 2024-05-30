<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class sector extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $fillable = [
        'city_id', 'lat', 'lng', 'blocked'
    ];
}
