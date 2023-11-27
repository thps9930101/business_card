<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Media extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    /* cast finish_time to carbon */
    protected $casts = [
        'finish_time' => 'datetime',
    ];

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
     * belongs to album
     */
    public function album(){
        return $this->belongsTo(Album::class);
    }

    /**
     * belongs to device
     */
    public function device(){
        return $this->belongsTo(Device::class);
    }


    public function getResorceMediaType(){
        return match($this->type){
            0 => 'video',
            1 => 'image',
            default => 'unknown',
        };
    }
}
