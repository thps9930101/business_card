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
        Schema::create('member_solution_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('member_solution_id')->constrained()->onDelete('cascade');
            $table->integer('times')->default(0);
            $table->String('expired_times')->default(0);
            $table->dateTime('expired_at')->nullable();
            $table->dateTime('next_expired_at')->nullable();
            $table->boolean('is_activated')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_solution_orders');
    }
};
