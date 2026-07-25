<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('quote_requests', function (Blueprint $table) {
            $table->id();
            
            // Relación con el usuario logueado
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Los nuevos campos de tu formulario simplificado
            $table->string('nombre');
            $table->string('telefono');
            $table->integer('cantidad')->default(1);
            $table->string('medida_cm');
            $table->string('acabado');
            $table->string('colores')->nullable(); // Ahora es un texto libre
            $table->text('descripcion_diseno_especial')->nullable();
            $table->string('imagen_path')->nullable(); // Ruta de la foto comprimida
            $table->string('estado')->default('pendiente'); // pendiente, aprobado, etc.
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quote_requests');
    }
};