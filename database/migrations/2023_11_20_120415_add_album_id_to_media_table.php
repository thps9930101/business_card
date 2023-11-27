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
        Schema::table('media', function (Blueprint $table) {
            //forign product_id
            $table->foreignId('album_id')->after('order_id')->nullable()->constrained()->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('media', function (Blueprint $table) {
            //
            if (Schema::hasColumn('media', 'album_id')) {
                $table->dropForeign('media_album_id_foreign');
                $table->dropColumn(['album_id']);
            }
        });
    }
};
