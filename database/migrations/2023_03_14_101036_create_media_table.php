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
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            //forign user_id
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            //forign order_id
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            //type 0 video 1 image
            $table->tinyInteger('type')->default(0);
            //cover image
            $table->string('cover')->nullable();
            //obj
            $table->string('obj')->nullable();
            //status 0 not process 1 done
            $table->boolean('status')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
