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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            //forign user_id
            $table->foreignId('user_id')->nullable()->index();
            //forign project_id
            $table->foreignId('project_id')->nullable()->index();
            //forign order_id
            $table->foreignId('order_id')->nullable()->index();
            $table->string('payment_method');
            $table->string('event_type')->nullable();
            $table->integer('payment_amount')->default(0);
            $table->string('payment_currency')->nullable();
            $table->string('transaction_id')->nullable();
            $table->string('status')->nullable();
            $table->string('summary')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
