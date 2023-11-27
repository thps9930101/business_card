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
        /* Schema::table('orders', function (Blueprint $table) {
            //
            $table->renameColumn('type', 'upload_type');
            
        }); */
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        /* Schema::table('orders', function (Blueprint $table) {
            //
            if (Schema::hasColumn('orders', 'upload_type')) {
                $table->renameColumn('upload_type', 'type');
            }
        }); */
    }
};
