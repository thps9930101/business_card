<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Media extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * belongs to user
     */
    public function user(){
        return $this->belongsTo(User::class);
    }

    /**
     * belongs to order
     */
    public function order(){
        return $this->belongsTo(Order::class);
    }

    /**
     * getter for status
     */
    public function status(): Attribute
    {
        return Attribute::make(
            get: fn($value) => match($value){
                false => '尚未處理',
                true => '已經處理',
                default => 'unknown',
            },
        );
    }

    /**
     * getter for type
     */
    public function type(): Attribute
    {
        return Attribute::make(
            get: fn($value) => match($value){
                0 => '影片',
                1 => '照片',
                default => 'unknown',
            },
        );
    }
}
