<?php

namespace Database\Seeders;

use App\Models\Persona;
use App\Models\Profesional;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ProfesionalSeeder extends Seeder
{
    public function run(): void
    {
        $persona = Persona::firstWhere('dni', '12345678');
        //Desde el comienzo del proyecto usamos siempre el usuario
        //de lucia.g como prueba
        Profesional::firstOrCreate(
            ['usuario' => 'lucia.g'],
            [
                'profesion'    => 'Psicopedagoga',
                'siglas'       => 'PS',
                'telefono'     => '2901-123456',
                'email'        => 'lucia@example.com',
                'contrasenia'  => Hash::make('segura123'),
                'fk_id_persona' => $persona->id_persona,
                'activo'       => true,
            ]
        );

        Profesional::factory()->count(3)->create();
    }
}
