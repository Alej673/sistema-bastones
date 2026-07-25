<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quote_requests', function (Blueprint $table) {
            $table->id();
            
            // 1. RELACIÓN Y CANTIDAD
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('cantidad_bastones')->default(1);
            
            // 2. ESTRUCTURA BASE
            $table->enum('medida_cm', [45, 50, 55, 60]);
            $table->enum('acabado', ['Plata', 'Dorado']);
            
            // 3. CUERPO (LANA)
            $table->integer('cantidad_colores_cuerpo')->default(1); // 1 a 3
            $table->json('colores_cuerpo'); // Arreglo de colores
            
            // 4. CORTINAS (OPCIONALES)
            $table->boolean('incluye_cortina_lana')->default(false);
            $table->json('colores_cortina_lana')->nullable(); 
            
            $table->boolean('incluye_cortina_fiesta')->default(false);
            $table->json('colores_cortina_fiesta')->nullable();
            
            // 5. DECORACIÓN Y APLIQUES
            $table->string('color_lazo_simple')->nullable(); // Null si no lo quiere
            $table->string('color_lazo_nombre')->nullable(); // Null si no lo quiere
            $table->integer('cantidad_flores')->default(0); // 0 a 6
            $table->json('colores_flores')->nullable();
            
            // 6. DETALLES MANUALES Y DISEÑOS ESPECIALES (Lo que evalúa el taller)
            $table->text('descripcion_apliques')->nullable(); // El cliente describe, tu mamá cobra los $0.50 c/u
            $table->text('descripcion_diseno_especial')->nullable(); // El cliente explica, tu mamá asigna la complejidad ($1.50, $2.00, $3.00)
            
            // 7. GESTIÓN DE PRECIOS Y ESTADOS
            $table->decimal('precio_referencial', 8, 2)->default(0.00); 
            $table->decimal('precio_final', 8, 2)->nullable();
            
            $table->enum('estado', [
                'pendiente', 
                'cotizado', 
                'en_produccion', 
                'entregado', 
                'cancelado'
            ])->default('pendiente');
            $table->text('observaciones_taller')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quote_requests');
    }
};