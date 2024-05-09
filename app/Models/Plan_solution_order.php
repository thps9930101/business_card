<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan_solution_order extends Model
{
    use HasFactory;
    
    protected $casts = [
        'expired_at' => 'datetime'
    ];

    //belongs to 1 order
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    //belongs to 1 product
    public function plan_solution()
    {
        return $this->belongsTo(Plan_solution::class);
    }
}
