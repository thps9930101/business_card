<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class price_menu extends Model
{
    use HasFactory;
 
    /**
     * 資料庫表的名稱
     *
     * @var string
     */
    protected $table = 'price_menu';

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
        'name',
        'times',
        'price',
        'bonus_times',
        'is_actived'
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
    public $timestamps = false;


}
