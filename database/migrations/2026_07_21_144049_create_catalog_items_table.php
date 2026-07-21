<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catalog_items', function (Blueprint $table) {
            $table->id();
            $table->string('titulo'); // Ej: "Bastón de Gala Clásico"
            $table->text('descripcion')->nullable(); // Ej: "Estructura resistente con acabados..."
            $table->string('imagen_path'); // Aquí guardaremos la ruta donde se guardó la foto
            
            // Este campo le permite a tu mamá ocultar un bastón de la página sin tener que borrarlo
            $table->boolean('activo')->default(true); 
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalog_items');
    }
};
