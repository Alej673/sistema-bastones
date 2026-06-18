<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // Subir datos a la base de datos para la creacion de la tabla cotizacion insumo
    public function up(): void
    {
        Schema::create('cotizacion_insumo', function (Blueprint $table) {
            $table->id();
            // Claves foráneas 
            $table->foreignId('cotizacion_id')->constrained('cotizaciones')->onDelete('cascade');
            $table->foreignId('insumo_id')->constrained('insumos')->onDelete('cascade');
            
            $table->decimal('cantidad_requerida', 8, 2); // Datos de cuanto de este material se usó
            $table->decimal('subtotal_calculado', 8, 2); // Datos de los costos de esa cantidad específica
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cotizacion_insumo');
    }
};
