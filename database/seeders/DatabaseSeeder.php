<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. USUARIO ROOT / DEMO (Para el Portafolio de GitHub y la Defensa de Grado)
        User::create([
            'name' => 'Administrador Demo',
            'email' => 'admin@demo.com',
            'password' => bcrypt('admin123'), // Una clave genérica y fácil de recordar
        ]);

        // 2. USUARIO REAL DE PRODUCCIÓN (Para el despliegue final en el taller)
        // Puedes dejarlo comentado en GitHub para proteger la privacidad si lo deseas
        User::create([
            'name' => 'Admin Taller',
            'email' => 'cristinatenelema2018@gmail.com',
            'password' => bcrypt('Daron-102'),
        ]);
    }
}
