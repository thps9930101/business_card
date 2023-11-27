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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            //forign store_id
            $table->foreignId('store_id')->constrained()->onDelete('cascade');
            // product costs
            $table->integer('costs')->default(0);
            // 0: photo; 1:album
            $table->tinyInteger('type')->default(0);
            $table->foreignId('media_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('album_id')->nullable()->constrained()->onDelete('cascade');
            $table->boolean('is_activated')->default(false);
            // 0:can update; 1:can't update
            $table->tinyInteger('status')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
