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
        Schema::table('product_solution_orders', function (Blueprint $table) {
            //
            $table->tinyInteger('status')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_solution_orders', function (Blueprint $table) {
            //
            if (Schema::hasColumn('product_solution_orders', 'status')) {
                // The "users" table exists and has an "email" column...
                $table->dropColumn(['status']);
            }
        });
    }
};
