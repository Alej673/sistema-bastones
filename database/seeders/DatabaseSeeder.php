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
        // 1. USUARIO ROOT / DEMO (Con Super Rol)
        User::create([
            'name' => 'Administrador Demo',
            'email' => 'admin@demo.com',
            'password' => bcrypt('admin123'),
            'role' => 'super_admin', // <-- Cambiado a super_admin
        ]);

        // 2. USUARIO DE ADMINISTRACIÓN (Taller)
        User::create([
            'name' => 'Admin Taller',
            'email' => 'taller@demo.com',
            'password' => bcrypt('admin123'),
            'role' => 'admin',
        ]);

        // 3. LLAMAR A LOS DEMÁS SEEDERS
        $this->call([
            AjustesTableSeeder::class,
        ]);
    }
}