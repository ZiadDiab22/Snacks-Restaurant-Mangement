<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class question extends Model
{
    use HasFactory;

    protected $fillable = [
        'question',
        'user_id',
        'emp_id',
        'answer'
    ];
}
