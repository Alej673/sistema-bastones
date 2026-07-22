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
            // Agregamos las dos banderas booleanas por defecto en 'false'
            $table->boolean('en_carrusel')->default(false)->after('activo');
            $table->boolean('es_destacado')->default(false)->after('en_carrusel');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('catalog_items', function (Blueprint $table) {
            // Si hacemos rollback, eliminamos estas columnas
            $table->dropColumn(['en_carrusel', 'es_destacado']);
        });
    }
};