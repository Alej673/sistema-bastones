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
        Schema::create('ajuste_tallers', function (Blueprint $table) {
            $table->id();
            $table->string('clave')->unique(); // ej: 'telefono_whatsapp'
            $table->text('valor')->nullable();  // ej: '593999856725'
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ajuste_tallers');
    }
};
