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

        // 2. USUARIO REAL DE PRODUCCIÓN (Administrador del Taller)
        User::create([
            'name' => 'Admin Taller',
            'email' => 'cristinatenelema2018@gmail.com',
            'password' => bcrypt('Daron-102'),
            'role' => 'admin', // <-- Mantiene rol de administración del taller
        ]);
    }
}