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
        Schema::table('orders', function (Blueprint $table) {
            //
            $table->integer('type')->comment('0:2to3 1:bought 2:add value 3:in store')->change(); // after('user_id')->
            $table->bigInteger('source_id')->unsigned()->index()->after('type')->index()->default(0);
            $table->integer('points')->default(0);
            $table->integer('free_points')->default(0)->after('points');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

        Schema::table('orders', function (Blueprint $table) {
            // 
            if (Schema::hasColumn('orders', 'source_id')) {
                $table->dropColumn(['source_id','points','free_points']);
            }
            
        });
    }
};
