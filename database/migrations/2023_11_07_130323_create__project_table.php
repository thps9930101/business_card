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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('costs')->default(0);
            $table->integer('points')->default(0);
            $table->boolean('is_activate')->default(false);
            $table->dateTime('start_at');
            $table->dateTime('end_at')->nullable();;
            $table->tinyInteger('type')->default(0)->comment('0:TBD');
            $table->string('description')->nullable();
            $table->string('icon')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
