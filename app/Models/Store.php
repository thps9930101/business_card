<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    use HasFactory;

    /**
     * guarded
     */
    protected $guarded = ['id'];

    /**
     * has many users
     */
    public function users(){
        return $this->hasMany(User::class);
    }
}
