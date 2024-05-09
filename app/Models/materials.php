<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class materials extends Model
{
    use HasFactory;

    /**
     * 資料庫表的名稱
     *
     * @var string
     */
    protected $table = 'materials';

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
        'card_url',
        'created_at',
        'updated_at'
    ];

    /**
     * 不可批量賦值的欄位
     *
     * @var array
     */
    protected $guarded = [
        // 需要保護的欄位...
    ];

    /**
     * 是否使用時間戳記
     *
     * @var bool
     */
    public $timestamps = true;
}
