<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    /**
     * belongs to store
     */
    public function store(){
        return $this->belongsTo(Store::class);
    }

    /**
     * belongs to media
     */
    public function media(){
        return $this->belongsTo(Media::class);
    }

    /**
     * belongs to album
     */
    public function album(){
        return $this->belongsTo(Album::class);
    }
}
