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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            // Relacionamos el comentario con el usuario que lo escribió
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('contenido');
            $table->integer('calificacion'); // Guardará un número del 1 al 5
            // Por defecto, los comentarios son públicos. Puedes ponerlo en false si quieres aprobarlos primero.
            $table->boolean('activo')->default(true); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
