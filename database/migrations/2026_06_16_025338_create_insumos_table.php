<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // Subir daros a la base de datos
    public function up(): void
    {
        Schema::create('insumos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('categoria'); // NUEVO: lana, base, cinta, cortina_fiesta, aplique
            $table->string('unidad_medida'); // Gramos, Metros, Unidades
            $table->decimal('costo_unitario', 8, 4); // 4 decimales para costos exactos (ej. 0.0127)
            $table->decimal('stock_actual', 8, 2)->default(0); // 2 decimales para gramos/metros
            $table->decimal('stock_minimo', 8, 2)->default(10); // 2 decimales
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('insumos');
    }
};
