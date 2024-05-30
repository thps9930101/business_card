<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('price_menu', function (Blueprint $table) {
            $table->integer('bonus_times')->after('price')->default(0); 
            $table->string('name')->after('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('price_menu', function (Blueprint $table) {
            $table->dropColumn('bonus_times');
            $table->dropColumn('name');
        });
    }
};
