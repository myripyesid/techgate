<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@techgate.com'],
            [
                'nombre' => 'Administrador TechGate',
                'telefono' => '3000000000',
                'contraseña' => 'admin1234',
                'rol' => User::ROL_ADMINISTRADOR,
            ]
        );

        User::updateOrCreate(
            ['email' => 'usuario@techgate.com'],
            [
                'nombre' => 'Usuario de prueba',
                'telefono' => '3001111111',
                'contraseña' => 'usuario1234',
                'rol' => User::ROL_USUARIO,
            ]
        );
    }
}
