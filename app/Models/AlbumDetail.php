<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AlbumDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'album_id',
        'media_id',
    ];

    public function album(){
        return $this->belongsTo(Album::class);
    }

    //has many media
    public function media()
    {
        return $this->belongsTo(Media::class);
    }
}
