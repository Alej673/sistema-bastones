<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Agregamos el borrado lógico a los Insumos
        Schema::table('insumos', function (Blueprint $table) {
            $table->softDeletes(); // Crea una columna 'deleted_at'
        });

        // 2. Quitamos la restricción ENUM de movimientos para poder escribir cualquier tipo
        Schema::table('movimientos', function (Blueprint $table) {
            $table->string('tipo_movimiento')->change();
        });
    }

    public function down(): void
    {
        Schema::table('insumos', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
