<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = ['id'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'confirm_code_expired_at'=>'datetime'
    ];

    /**
     * Interact with the user's first name.
     */
    protected function phone(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => str_replace('-','',$value),//format can be either 09xx-xxx-xxx or 09xxxxxxxx and should be cast to 09xxxxxxxx
            set: fn (string $value) => str_replace('-','',$value),//format can be either 09xx-xxx-xxx or 09xxxxxxxx and should be cast to 09xxxxxxxx
        );
    }

    /**
     * has many devices
     */
    public function devices(){
        return $this->hasMany(Device::class);
    }

    /**
     * has many medias
     */
    public function media(){
        return $this->hasMany(Media::class);
    }

    /**
     * has many orders
     */
    public function orders(){
        return $this->hasMany(Order::class);
    }

    /**
     * belongs to store
     */
    public function store(){
        return $this->belongsTo(Store::class);
    }


}
