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
        Schema::table('pedidos', function (Blueprint $table) {
            // Llave foránea opcional (nullable)
            $table->unsignedBigInteger('quote_request_id')->nullable()->after('id');
            
            $table->foreign('quote_request_id')
                ->references('id')
                ->on('quote_requests')
                ->onDelete('set null'); 
        });
    }

    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropForeign(['quote_request_id']);
            $table->dropColumn('quote_request_id');
        });
    }
};
