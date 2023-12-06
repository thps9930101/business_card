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
        Schema::create('product_solutions', function (Blueprint $table) {
            $table->id();
            //forign store_id
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            // product costs
            $table->integer('costs')->default(0);
            $table->float('period')->default(0);
            $table->boolean('is_activated')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_solutions');
    }
};
