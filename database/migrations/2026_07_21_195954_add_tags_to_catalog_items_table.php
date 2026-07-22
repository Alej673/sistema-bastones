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
        Schema::table('catalog_items', function (Blueprint $table) {
            $table->string('categoria')->default('baston')->after('activo');
            $table->string('medida_cm')->default('50')->after('categoria');
            $table->string('nivel_diseno')->default('basico')->after('medida_cm');
            $table->string('nivel_accesorios')->default('estandar')->after('nivel_diseno');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('catalog_items', function (Blueprint $table) {
            $table->dropColumn([
                'categoria', 
                'medida_cm', 
                'nivel_diseno', 
                'nivel_accesorios'
            ]);
        });
    }
};