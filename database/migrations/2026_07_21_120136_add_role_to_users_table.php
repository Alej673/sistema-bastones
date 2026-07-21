<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Agregamos el campo 'role' después del email. 
            // Por defecto, cualquiera que se registre será 'cliente'.
            $table->enum('role', ['admin', 'cliente'])->default('cliente')->after('email');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Si revertimos la migración, borramos la columna
            $table->dropColumn('role');
        });
    }
};
