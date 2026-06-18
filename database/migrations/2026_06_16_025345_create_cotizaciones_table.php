<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // Subir datos a la base de datos para la creacion de la tabla cotizaciones
    public function up(): void
    {
        Schema::create('cotizaciones', function (Blueprint $table) {
            $table->id();
            $table->string('cliente_nombre');
            $table->string('cliente_email')->nullable();
            $table->string('estado')->default('Pendiente'); // Trabajar con los estados de la cotizacion.
            $table->decimal('costo_operativo', 8, 2); // Costo por mano de obra
            $table->decimal('costo_total', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cotizaciones');
    }
};
