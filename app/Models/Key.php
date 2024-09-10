<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Key extends Model
{
    use HasFactory;

    /**
     * 資料庫表的名稱
     *
     * @var string
     */
    protected $table = 'keys';

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
        'public_id',
        'key_value',
        'created_at',
        'updated_at',
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

    /**
     * 關聯到 cards 表
     */
    public function card()
    {
        return $this->belongsTo(Card::class, 'public_id', 'public_id');
    }
}
