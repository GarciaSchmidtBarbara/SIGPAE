<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Persona;
use App\Models\Profesional;
use App\Models\Aula;
use App\Models\Alumno;
use App\Models\PlanDeAccion;
use App\Models\Intervencion;
use Illuminate\Support\Facades\Hash;


class BaseInstitucionalSeeder extends Seeder
{
    public function run(): void
    {
        $persona = Persona::firstOrCreate(
            ['dni' => '12345678'], // condición para buscar
            [ // datos para crear si no existe
            'nombre' => 'Lucía',
            'apellido' => 'González',
            'fecha_nacimiento' => '1990-05-12',
            'domicilio' => 'Av. San Martín 123',
            'nacionalidad' => 'Argentina',
        ]);
        Profesional::firstOrCreate(
            ['usuario' => 'lucia.g'], // condición para buscar
            [
            'profesion' => 'Psicopedagoga',
            'siglas' => 'PS',
            'telefono' => '2901-123456',
            'email' => 'lucia@example.com',
            'contrasenia' => Hash::make('segura123'),
            'fk_id_persona' => $persona->id_persona,
        ]);

        Profesional::factory()->count(3)->create();

        
        \App\Models\Aula::factory()->count(10)->create();
        

        \App\Models\Alumno::factory()->count(10)->create();
        \App\Models\Intervencion::factory()->count(10)->create();
        
        // Crear Planes Individuales (con 1 Alumno y 1 profesional participante)
        PlanDeAccion::factory()->count(5)->individual()->create();

        // Crear Planes Grupales (con Aulas y múltiples Alumnos/Responsables)
        PlanDeAccion::factory()->count(6)->grupal()->create();

        // Crear Planes Institucionales (con múltiples responsables)
        PlanDeAccion::factory()->count(2)->institucional()->create();

    }
}
