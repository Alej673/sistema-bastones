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
            $table->foreignId('pedido_id')->constrained('pedidos')->onDelete('cascade');
            
            // El ID puede ser nulo si es un material fantasma
            $table->foreignId('insumo_id')->nullable()->constrained('insumos');
            
            // Guardamos el nombre tal cual (Ej: "Lana Azul" o el inventado "Satin Rosa")
            $table->string('nombre_material'); 
            
            $table->decimal('cantidad_requerida', 8, 2);
            $table->decimal('subtotal_calculado', 8, 2);
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
