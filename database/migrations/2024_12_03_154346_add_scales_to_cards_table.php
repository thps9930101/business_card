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
        Schema::table('cards', function (Blueprint $table) {
            $table->float('model_scale')->default(1.0)->comment('模型縮放比例');
            $table->float('pic_front_scale')->default(1.0)->comment('正面圖片縮放比例');
            $table->float('pic_back_scale')->default(1.0)->comment('背面圖片縮放比例');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cards', function (Blueprint $table) {
            $table->dropColumn(['model_scale', 'pic_front_scale', 'pic_back_scale']);
        });
    }
};
