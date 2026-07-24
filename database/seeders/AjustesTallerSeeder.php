<?php

namespace Database\Seeders;

use App\Models\AjusteTaller;
use Illuminate\Database\Seeder;

class AjustesTallerSeeder extends Seeder
{
    public function run(): void
    {
        AjusteTaller::updateOrCreate(
            ['clave' => 'telefono_whatsapp'],
            ['valor' => '593999856725']
        );
        
        AjusteTaller::updateOrCreate(
            ['clave' => 'direccion'],
            ['valor' => 'Quito, Ecuador']
        );
    }
}