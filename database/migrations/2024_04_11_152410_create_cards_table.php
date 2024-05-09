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
        Schema::create('cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('fax')->nullable();
            $table->string('edit_name')->uniqid();
            $table->string('release_name')->uniqid();
            $table->foreignId('model_id')->constrained()->onDelete('cascade');
            $table->foreignId('card_front_id')->constrained('materials')->nullable()->onDelete('cascade');    
            $table->foreignId('card_back_id')->constrained('materials')->nullable()->onDelete('cascade');     
             $table->string('telegram')->nullable();
            $table->string('whatsapp')->nullable();
            $table->string('facebook')->nullable();
            $table->string('instagram')->nullable();
            $table->string('X')->nullable();
            $table->string('web')->nullable();
            $table->boolean('is_actived')->default(0);
            $table->integer('download_time')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cards');
    }
};
