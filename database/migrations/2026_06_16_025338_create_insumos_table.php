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
            $table->string('categoria'); 
            $table->string('unidad_medida'); 
            $table->decimal('costo_unitario', 8, 4); 
            $table->decimal('stock_actual', 8, 2)->default(0); 
            $table->decimal('stock_minimo', 8, 2)->default(10); 
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
