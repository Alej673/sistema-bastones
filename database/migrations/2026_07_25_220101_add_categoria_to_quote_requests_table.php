<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('quote_requests', function (Blueprint $table) {
            // Agregamos la columna 'categoria' después de 'estado' para mantener el orden lógico
            $table->string('categoria')->nullable()->after('estado');
        });
    }

    public function down()
    {
        Schema::table('quote_requests', function (Blueprint $table) {
            // Si algún día haces un rollback, esto borra la columna sin dañar la tabla
            $table->dropColumn('categoria');
        });
    }
};