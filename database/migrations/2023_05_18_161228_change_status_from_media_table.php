<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('media', function (Blueprint $table) {
            /*
                0: 處理中
                1: 已完成
                2: 處理失敗
                3: 已刪除
             */
            $table->smallInteger('status')->default(0)->comment('0: 處理中, 1: 已完成, 2: 處理失敗, 3: 已刪除')->change();
            //
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('media', function (Blueprint $table) {
            $table->boolean('status')->default(0)->change();
            //
        });
    }
};
