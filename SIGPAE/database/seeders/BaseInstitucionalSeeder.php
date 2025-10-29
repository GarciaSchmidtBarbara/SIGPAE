<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\Personas\Models\Persona;
use App\Modules\Profesionales\Models\Profesional;
use Illuminate\Support\Facades\Hash;

class BaseInstitucionalSeeder extends Seeder
{
    public function run(): void
    {
        $persona = Persona::create([
            'nombre' => 'Lucía',
            'apellido' => 'González',
            'dni' => '12345678',
            'fecha_nacimiento' => '1990-05-12',
            'domicilio' => 'Av. San Martín 123',
            'nacionalidad' => 'Argentina',
        ]);

        Profesional::create([
            'profesion' => 'Psicopedagoga',
            'telefono' => '2901-123456',
            'usuario' => 'lucia.g',
            'email' => 'lucia@example.com',
            'password' => Hash::make('segura123'),
            'fk_id_persona' => $persona->id_persona,
        ]);

    }
}
