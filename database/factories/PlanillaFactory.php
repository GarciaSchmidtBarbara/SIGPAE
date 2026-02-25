<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Planilla>
 */
class PlanillaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'anio' => 2025,
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            // Valores por defecto para que no falle si no usamos un estado
            'tipo_planilla' => 'GENÉRICA',
            'nombre_planilla' => 'Planilla Genérica',
            'datos_planilla' => [], 
        ];
    }

    // --- ESTADO 1: ACTA DE EQUIPO (EI) ---
    public function actaEquipo()
    {
        return $this->state(function (array $attributes) {
            
            $participantes = [];
            for ($i = 0; $i < 6; $i++) {
                $participantes[] = [
                    'cargo' => $this->faker->randomElement(['EI', 'Director', 'Docente']),
                    'nombre' => $this->faker->name(),
                    'asistio' => $this->faker->boolean(80)
                ];
            }

            return [
                'tipo_planilla' => 'ACTA REUNIÓN EQUIPO INTERDISCIPLINARIO- equipo directivo',
                'nombre_planilla' => 'Acta EI Directivo - ' . $this->faker->date(),
                'datos_planilla' => [
                    'grado'           => $this->faker->randomElement(['1 A', '2 B', '3 C', '4 A', '5 B']),
                    'fecha'           => $this->faker->date(),
                    'hora'            => $this->faker->time('H:i'),
                    'participantes'   => $participantes,
                    'temario'         => $this->faker->paragraph(3),
                    'acuerdo'         => $this->faker->paragraph(2),
                    'observaciones'   => $this->faker->sentence(),
                    'proxima_reunion' => $this->faker->date(),
                ],
            ];
        });
    }

    // --- ESTADO 2: PLANILLA MEDIAL ---
    public function planillaMedial()
    {
        return $this->state(function (array $attributes) {
            
            $filas = [];
            for ($i = 0; $i < 4; $i++) {
                $filas[] = [
                    'nombre' => $this->faker->name(),
                    'grado' => $this->faker->randomElement(['1', '2', '3', '4']),
                    'motivo' => $this->faker->sentence(),
                    'descripcion' => $this->faker->text(50),
                    'modalidad' => 'Presencial',
                    'profesionales' => $this->faker->name()
                ];
            }

            return [
                'tipo_planilla' => 'PLANILLA MEDIAL',
                'nombre_planilla' => 'Medial Esc. ' . $this->faker->company() . ' - ' . $this->faker->date(),
                'datos_planilla' => [
                    'anio_escolar' => 2025,
                    'fecha'        => $this->faker->date(),
                    'escuela'      => 'Escuela N° ' . $this->faker->numberBetween(1, 100),
                    'tabla_medial' => $filas,
                ],
            ];
        });
    }
    
    // --- ESTADO 3: ACTA BANDA (Docentes) ---
    public function actaBanda()
    {
        return $this->state(function (array $attributes) {
            
            $participantes = [];
            for ($i = 0; $i < 8; $i++) {
                $participantes[] = [
                    'cargo' => $this->faker->jobTitle(),
                    'nombre' => $this->faker->name(),
                    'asistio' => true
                ];
            }

            return [
                'tipo_planilla' => 'ACTA REUNIÓN DE TRABAJO - EQUIPO DIRECTIVO - EI - DOCENTES (BANDA)',
                'nombre_planilla' => 'Acta Banda - ' . $this->faker->date(),
                'datos_planilla' => [
                    'grado'           => 'General',
                    'fecha'           => $this->faker->date(),
                    'hora'            => $this->faker->time('H:i'),
                    'participantes'   => $participantes,
                    'temario'         => $this->faker->paragraph(),
                    'acuerdo'         => $this->faker->paragraph(),
                    'observaciones'   => 'Sin novedades.',
                    'proxima_reunion' => null,
                ],
            ];
        });
    }
}