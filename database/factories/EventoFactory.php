<?php

namespace Database\Factories;

use App\Models\Evento;
use App\Models\Profesional;
use App\Models\Alumno;
use App\Models\Aula;
use App\Enums\TipoEvento;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventoFactory extends Factory
{
    protected $model = Evento::class;

    public function definition(): array
    {
        $tipoEvento = $this->faker->randomElement(TipoEvento::cases());
        
        return [
            'fecha_hora' => $this->faker->dateTimeBetween('-1 month', '+2 months')
                ->setTime(fake()->numberBetween(8, 18), fake()->randomElement([0, 15, 30, 45])),
            'lugar' => $this->faker->randomElement([
                'Sala de reuniones',
                'Aula 1',
                'Aula 2',
                'Biblioteca',
                'Patio central',
                'Sala de profesores',
                'Despacho del director',
                'Consultorio externo'
            ]),
            'tipo_evento' => $tipoEvento,
            'notas' => $this->faker->optional(0.6)->sentence(8),
            'profesional_tratante' => $this->faker->optional(0.3)->name(),
            'periodo_recordatorio' => $this->faker->optional(0.5)->randomElement([1, 3, 7, 15]),
            'fk_id_profesional_creador' => Profesional::inRandomOrder()->first()?->id_profesional ?? Profesional::factory()->create()->id_profesional,
        ];
    }

    
    public function configure()
    {
        return $this->afterCreating(function (Evento $evento) {
            //Para DERIVACION_EXTERNA no agregamos profesionales invitados
            if ($evento->tipo_evento !== TipoEvento::DERIVACION_EXTERNA) {
                $profesionales = Profesional::inRandomOrder()
                    ->take(fake()->numberBetween(1, 3))
                    ->get();
                
                foreach ($profesionales as $profesional) {
                    $evento->esInvitadoA()->create([
                        'fk_id_profesional' => $profesional->id_profesional,
                        'confirmacion' => fake()->boolean(70), // 70% confirmados
                        'asistio' => $evento->fecha_hora->isPast() ? fake()->boolean(80) : false,
                    ]);
                }
            }

            if ($evento->tipo_evento !== TipoEvento::RG) {
                $alumnos = Alumno::inRandomOrder()
                    ->take(fake()->numberBetween(1, 5))
                    ->pluck('id_alumno')
                    ->toArray();
                
                $evento->alumnos()->attach($alumnos);
            }

            if (in_array($evento->tipo_evento, [TipoEvento::BANDA, TipoEvento::RG])) {
                $aulas = Aula::inRandomOrder()
                    ->take(fake()->numberBetween(1, 2))
                    ->pluck('id_aula')
                    ->toArray();
                
                $evento->aulas()->attach($aulas);
            }
        });
    }

    public function banda(): static
    {
        return $this->state(fn (array $attributes) => [
            'tipo_evento' => TipoEvento::BANDA,
            'lugar' => fake()->randomElement(['Aula 1', 'Aula 2', 'Sala de reuniones']),
        ]);
    }

    public function reunionGabinete(): static
    {
        return $this->state(fn (array $attributes) => [
            'tipo_evento' => TipoEvento::RG,
            'lugar' => 'Sala de profesores',
        ]);
    }

 
    public function reunionDerivacion(): static
    {
        return $this->state(fn (array $attributes) => [
            'tipo_evento' => TipoEvento::RD,
            'lugar' => fake()->randomElement(['Sala de reuniones', 'Despacho del director']),
        ]);
    }

    public function citaFamiliar(): static
    {
        return $this->state(fn (array $attributes) => [
            'tipo_evento' => TipoEvento::CITA_FAMILIAR,
            'lugar' => fake()->randomElement(['Sala de reuniones', 'Consultorio']),
        ]);
    }

    public function derivacionExterna(): static
    {
        return $this->state(fn (array $attributes) => [
            'tipo_evento' => TipoEvento::DERIVACION_EXTERNA,
            'lugar' => 'Consultorio externo',
            'notas' => fake()->optional(0.5)->sentence(8),
        ]);
    }

    public function pasado(): static
    {
        return $this->state(fn (array $attributes) => [
            'fecha_hora' => fake()->dateTimeBetween('-2 months', '-1 day')
                ->setTime(fake()->numberBetween(8, 18), fake()->randomElement([0, 15, 30, 45])),
        ]);
    }

    public function futuro(): static
    {
        return $this->state(fn (array $attributes) => [
            'fecha_hora' => fake()->dateTimeBetween('+1 day', '+3 months')
                ->setTime(fake()->numberBetween(8, 18), fake()->randomElement([0, 15, 30, 45])),
        ]);
    }
}
