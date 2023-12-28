<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    //guarded
    protected $guarded = ['id'];

    /**
     * belongs to user
     */
    public function user(){
        return $this->belongsTo(User::class);
    }


    /**
     * belongs to product_solution
     */
    public function product_solution()
    {
        return $this->belongsTo(Product_solution::class);
    }
}
