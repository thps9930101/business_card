<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    //guarded
    protected $guarded = ['id'];

    //belongs to user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    //belongs to 1 product
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    //has 1 product_solutions
    public function product_solution_order()
    {
        return $this->hasOne(Product_solution_order::class);
    }

    //has 1 product_solutions
    public function plan_solution_order()
    {
        return $this->hasOne(Plan_solution_order::class);
    }

    //has many media
    public function media()
    {
        return $this->hasMany(Media::class);
    }

    public function getResourceStatus(){
        return match($this->status){
            0 => 'Order Placed',
            1 => 'Processing',
            2 => 'Finished',
            3 => 'Cancelled',
            default => 'unknown',
        };
    }
}
