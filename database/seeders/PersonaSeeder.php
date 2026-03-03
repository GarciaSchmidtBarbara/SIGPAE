<?php

namespace Database\Seeders;

use App\Models\Persona;
use Illuminate\Database\Seeder;

class PersonaSeeder extends Seeder
{
    public function run(): void
    {
        Persona::firstOrCreate(
            ['dni' => '12345678'],
            [
                'nombre'            => 'Lucía',
                'apellido'          => 'González',
                'fecha_nacimiento'  => '1990-05-12',
                'domicilio'         => 'Av. San Martín 123',
                'nacionalidad'      => 'Argentina',
            ]
        );
    }
}
