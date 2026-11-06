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
        // 1. USUARIO ROOT (Lee del .env en producción, usa Demo en local)
        User::create([
            'name' => env('SUPER_ADMIN_NAME', 'Administrador Demo'),
            'email' => env('SUPER_ADMIN_EMAIL', 'admin@demo.com'),
            'password' => bcrypt(env('SUPER_ADMIN_PASSWORD', 'admin123')),
            'role' => 'super_admin', 
        ]);

        // 2. USUARIO DE ADMINISTRACIÓN / TALLER (Lee del .env en producción, usa Demo en local)
        User::create([
            'name' => env('ADMIN_TALLER_NAME', 'Admin Taller'),
            'email' => env('ADMIN_TALLER_EMAIL', 'taller@demo.com'),
            'password' => bcrypt(env('ADMIN_TALLER_PASSWORD', 'admin123')),
            'role' => 'admin',
        ]);

        // 3. LLAMAR A LOS DEMÁS SEEDERS
        $this->call([
            AjustesTableSeeder::class,
        ]);
    }
}