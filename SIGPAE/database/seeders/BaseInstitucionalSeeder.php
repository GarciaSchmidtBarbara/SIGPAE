<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Persona;
use App\Models\Profesional;
use App\Models\Aula;
use App\Models\Alumno;
use App\Models\PlanDeAccion;
use App\Models\Intervencion;
use App\Models\Evento;
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
        
        
        // Crear Planes Individuales (con 1 Alumno y 1 profesional participante)
        PlanDeAccion::factory()->count(5)->individual()->create();

        // Crear Planes Grupales (con Aulas y múltiples Alumnos/Responsables)
        PlanDeAccion::factory()->count(6)->grupal()->create();

        // Crear Planes Institucionales (con múltiples responsables)
        PlanDeAccion::factory()->count(2)->institucional()->create();

        // Crear Intervenciones
        Intervencion::factory()->count(10)->create();

        // Crear Eventos variados
        // Eventos BANDA (reuniones de grupo)
        Evento::factory()->count(3)->banda()->create();
        
        // Eventos de Reunión de Gabinete
        Evento::factory()->count(2)->reunionGabinete()->create();
        
        // Eventos de Reunión Derivación
        Evento::factory()->count(2)->reunionDerivacion()->create();
        
        // Eventos de Cita Familiar
        Evento::factory()->count(4)->citaFamiliar()->create();
        
        // Eventos de Derivación Externa (sin profesionales invitados)
        Evento::factory()->count(3)->derivacionExterna()->create();
        
        // Algunos eventos adicionales aleatorios
        Evento::factory()->count(5)->create();
        
        // Algunos eventos en el pasado
        Evento::factory()->count(3)->pasado()->create();
        
        // Algunos eventos futuros
        Evento::factory()->count(3)->futuro()->create();
    }
}
