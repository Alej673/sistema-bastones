<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('pedido_materiales', function (Blueprint $table) {
            // Agregamos el flag, por defecto en false (no ignorada)
            $table->boolean('alerta_ignorada')->default(false)->after('insumo_id');
        });
    }

    public function down()
    {
        Schema::table('pedido_materiales', function (Blueprint $table) {
            $table->dropColumn('alerta_ignorada');
        });
    }
};
