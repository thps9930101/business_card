<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    //belongs to user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    //has many media
    public function media()
    {
        return $this->hasMany(Media::class);
    }

    //getter for status
    public function status(): Attribute
    {
        return Attribute::make(
            get: fn($value) => match($value){
                0 => 'Order Placed',
                1 => 'Processing',
                2 => 'Finished',
                3 => 'Cancelled',
                default => 'unknown',
            },
        );
    }
}
