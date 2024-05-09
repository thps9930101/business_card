<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class cards extends Model
{
    use HasFactory;

    /**
     * 資料庫表的名稱
     *
     * @var string
     */
    protected $table = 'cards';

    /**
     * 資料庫表的主鍵
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * 可以批量賦值的欄位
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'name',
        'email',
        'phone',
        'address',
        'fax',
        'edit_name',
        'release_name',
        'model_id',
        'card_front_id',
        'card_back_id',
        'telegram',
        'whatsapp',
        'facebook',
        'instagram',
        'X',
        'web',
        'is_actived',
        'download_time',
        'created_at',
        'updated_at'
    ];

    /**
     * 不可批量賦值的欄位
     *
     * @var array
     */
    protected $guarded = ['id'];


    /**
     * 是否使用時間戳記
     *
     * @var bool
     */
    public $timestamps = true;
}
