<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class city extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $fillable = [
        'lat',
        'lng',
        'country_id',
        'name'
    ];
}
