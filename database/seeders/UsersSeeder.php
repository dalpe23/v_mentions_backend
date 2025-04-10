<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    public function run()
    {
        // Crear usuario admin
        User::create([
            'name'     => 'Administrador',
            'email'    => 'admin@vmentions.com',
            'password' => Hash::make('1234'),
            'rol'      => 'admin',
        ]);

        // Crear usuario cliente de prueba
        User::create([
            'name'     => 'Cliente de Prueba',
            'email'    => 'cliente@vmentions.com',
            'password' => Hash::make('1234'),
            'rol'      => 'cliente',
        ]);
    }
}
