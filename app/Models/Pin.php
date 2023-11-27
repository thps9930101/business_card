<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pin extends Model
{
    use HasFactory;

    //Casts of the model dates
    protected $casts = [
        'start_at' => 'date',
        'end_at' => 'date',
    ];
}
