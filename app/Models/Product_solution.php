<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product_solution extends Model
{
    use HasFactory;

    
    //belongs to 1 product
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function product_solution_order()
    {
        return $this->hasMany(Product_solution_order::class);
    }
}
