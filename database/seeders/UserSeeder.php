<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@techgate.com'],
            [
                'name'     => 'Administrador TechGate',
                'telefono' => '3000000000',
                'password' => Hash::make('admin1234'),
                'rol'      => User::ROL_ADMINISTRADOR,
            ]
        );

        User::updateOrCreate(
            ['email' => 'usuario@techgate.com'],
            [
                'name'     => 'Usuario de prueba',
                'telefono' => '3001111111',
                'password' => Hash::make('usuario1234'),
                'rol'      => User::ROL_USUARIO,
            ]
        );
    }
}