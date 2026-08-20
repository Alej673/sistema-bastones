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
        Schema::create('ajustes', function (Blueprint $table) {
            $table->id();
            $table->string('llave')->unique(); // Ej: 'ganancia_base'
            $table->text('valor')->nullable(); // Ej: '0.60' (Usamos text por si algún día guardas un texto largo)
            $table->string('grupo')->nullable(); // Ej: 'finanzas', 'precios_fantasma'
            $table->string('descripcion')->nullable(); // Para que la administradora sepa qué hace cada cosa
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ajustes');
    }
};
