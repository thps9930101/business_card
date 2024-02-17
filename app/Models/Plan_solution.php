<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan_solution extends Model
{
    use HasFactory;
    
    public function plan_solution_order()
    {
        return $this->hasMany(Plan_solution_order::class);
    }
}
