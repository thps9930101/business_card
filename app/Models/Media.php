<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    use HasFactory;

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
}
