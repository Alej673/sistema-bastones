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
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            $table->string('cliente_nombre');
            $table->integer('cantidad_total_bastones'); // Cantidad de bastones del pedido (ej. 12)
            $table->decimal('total_precio_cliente', 8, 2); // Precio final cobrado al cliente
            
            // Estado de producción usando ENUM para evitar textos libres erróneos
            $table->enum('estado', ['pendiente', 'realizado', 'cancelado'])->default('pendiente');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedidos');
    }
};
