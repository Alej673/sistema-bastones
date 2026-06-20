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
        Schema::create('pedido_materiales', function (Blueprint $table) {
            $table->id();
            
            // Relación con la tabla pedidos (Si se elimina el pedido, se borra su receta)
            $table->foreignId('pedido_id')->constrained('pedidos')->onDelete('cascade');
            
            // Relación con la tabla insumos (Evita borrar lanas/bases si están amarradas a un pedido)
            $table->foreignId('insumo_id')->constrained('insumos')->onRestrict();
            
            // Cantidad exacta calculada que consumirá este pedido (en gramos, metros o unidades)
            $table->decimal('cantidad_estimada', 8, 2); 
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedido_materiales');
    }
};
