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
        // 1. USUARIO ROOT / DEMO
        User::create([
            'name' => 'Administrador Demo',
            'email' => 'admin@demo.com',
            'password' => bcrypt('admin123'),
            'role' => 'admin', // <-- Le asignas rol de administrador
        ]);

        // 2. USUARIO REAL DE PRODUCCIÓN
        User::create([
            'name' => 'Admin Taller',
            'email' => 'cristinatenelema2018@gmail.com',
            'password' => bcrypt('Daron-102'),
            'role' => 'admin', // <-- Le asignas rol de administrador
        ]);
    }
}