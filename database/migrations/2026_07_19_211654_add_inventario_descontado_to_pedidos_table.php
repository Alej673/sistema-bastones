<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('pedidos', function (Blueprint $table) {
            // Agregamos la columna como booleano (true/false), por defecto false
            $table->boolean('inventario_descontado')->default(false)->after('estado');
        });
    }

    public function down()
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropColumn('inventario_descontado');
        });
    }
};
