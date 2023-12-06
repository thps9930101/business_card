<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class product_solution_order extends Model
{
    use HasFactory;

    //belongs to 1 order
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    //belongs to 1 product
    public function product_solution()
    {
        return $this->belongsTo(Product_solution::class);
    }
}
