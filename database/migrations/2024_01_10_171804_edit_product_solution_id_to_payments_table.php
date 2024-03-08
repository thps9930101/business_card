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
        Schema::table('payments', function (Blueprint $table) {
            //forign project_id
            $table->foreignId('order_id')->after('product_solution_id')->nullable()->index();
            $table->dropColumn(['product_solution_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            //
            if (Schema::hasColumn('payments', 'order_id')) {
                $table->foreignId('product_solution_id')->after('order_id')->nullable()->index();
                $table->dropColumn(['order_id']);
            }
        });
    }
};
