<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Persona;
use App\Models\Profesional;
use App\Models\Aula;
use App\Models\Alumno;
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

        

        $aula = Aula::firstOrCreate(
            ['curso' => '2°', 'division' => 'B'], 
            [] 
        );

        $persona = Persona::firstOrCreate(
            ['dni' => '87654321'],
            [
                'nombre' => 'Mateo',
                'apellido' => 'Ramírez',
                'fecha_nacimiento' => '2008-03-25',
                'domicilio' => 'Calle Falsa 456',
                'nacionalidad' => 'Argentina',
            ]
        );
        Alumno::firstOrCreate(
            ['fk_id_persona' => $persona->id_persona],
            [
                'cud' => false,
                'inasistencias' => 5,
                'observaciones' => 'Participa activamente en clase',
                'antecedentes' => 'Retraso madurativo leve',
                'intervenciones_externas' => 'Apoyo psicopedagógico semanal',
                'actividades_extraescolares' => 'Fútbol y taller de robótica',
                'situacion_escolar' => 'Promovido con acompañamiento',
                'situacion_medica' => 'Sin patologías relevantes',
                'situacion_familiar' => 'Convive con madre y hermanos',
                'situacion_socioeconomica' => 'Ingreso familiar bajo, recibe ayuda escolar',
                'fk_id_aula' => $aula->id_aula,
            ]
        );

    }
}
