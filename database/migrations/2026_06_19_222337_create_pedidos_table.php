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
            
            // Datos del cliente
            $table->string('cliente_nombre')->default('Cliente de Mostrador');
            $table->string('correo_cliente')->nullable(); 
            
            $table->integer('cantidad_total_bastones'); 
            
            // Desglose Financiero 
            $table->decimal('costo_materiales', 8, 2)->default(0);
            $table->decimal('costo_extras', 8, 2)->default(0);
            $table->decimal('ganancia_fija', 8, 2)->default(0);
            $table->decimal('costo_total', 8, 2); 
            $table->decimal('costo_unitario', 8, 2)->default(0); 
            
            // Tu ENUM actualizado según el Documento Técnico (RF-05)
            $table->enum('estado', ['pendiente', 'en_produccion', 'realizado', 'cancelado'])->default('pendiente');
            
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
