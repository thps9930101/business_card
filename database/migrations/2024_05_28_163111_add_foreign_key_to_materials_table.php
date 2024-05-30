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
        Schema::table('materials', function (Blueprint $table) {
            //
            $table->unsignedBigInteger('company_id')->after('id')->nullable()->default(null); // 调整 'after' 参数以插入到正确的位置

            // 添加外键约束
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('materials', function (Blueprint $table) {
            // 删除外键约束
            $table->dropForeign(['company_id']);

            // 删除列
            $table->dropColumn('company_id');
        });
    }
};
