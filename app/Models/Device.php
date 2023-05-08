<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    use HasFactory;

    //guarded
    protected $guarded = ['id'];

    //belongs to user
    public function user(){
        return $this->belongsTo(User::class);
    }

    //has many media
    public function media(){
        return $this->hasMany(Media::class);
    }
}
