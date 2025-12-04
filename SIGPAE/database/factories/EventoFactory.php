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
        
        // Para DERIVACION_EXTERNA usamos un string en notas, para otros tipos dejamos notas normales
        $notas = $tipoEvento === TipoEvento::DERIVACION_EXTERNA 
            ? 'Profesional externo: ' . $this->faker->name() 
            : $this->faker->optional(0.6)->sentence(8); // Máximo 8 palabras para no exceder 255 caracteres

        return [
            'fecha_hora' => $this->faker->dateTimeBetween('-1 month', '+2 months'),
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
            'notas' => $notas,
            'profesional_tratante' => $this->faker->optional(0.3)->name(),
            'periodo_recordatorio' => $this->faker->optional(0.5)->randomElement([1, 3, 7, 15]),
            'fk_id_profesional_creador' => Profesional::inRandomOrder()->first()->id_profesional,
        ];
    }

    /**
     * Configurar el evento después de crearlo
     */
    public function configure()
    {
        return $this->afterCreating(function (Evento $evento) {
            // Para DERIVACION_EXTERNA no agregamos profesionales invitados
            if ($evento->tipo_evento !== TipoEvento::DERIVACION_EXTERNA) {
                // Agregar profesionales invitados (1-3 profesionales)
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

            // Agregar alumnos relacionados (1-5 alumnos para eventos que no sean RG)
            if ($evento->tipo_evento !== TipoEvento::RG) {
                $alumnos = Alumno::inRandomOrder()
                    ->take(fake()->numberBetween(1, 5))
                    ->pluck('id_alumno')
                    ->toArray();
                
                $evento->alumnos()->attach($alumnos);
            }

            // Agregar aulas para eventos BANDA o RG (1-2 aulas)
            if (in_array($evento->tipo_evento, [TipoEvento::BANDA, TipoEvento::RG])) {
                $aulas = Aula::inRandomOrder()
                    ->take(fake()->numberBetween(1, 2))
                    ->pluck('id_aula')
                    ->toArray();
                
                $evento->aulas()->attach($aulas);
            }
        });
    }

    /**
     * Estado para evento tipo BANDA
     */
    public function banda(): static
    {
        return $this->state(fn (array $attributes) => [
            'tipo_evento' => TipoEvento::BANDA,
            'lugar' => fake()->randomElement(['Aula 1', 'Aula 2', 'Sala de reuniones']),
        ]);
    }

    /**
     * Estado para evento tipo Reunión de Gabinete
     */
    public function reunionGabinete(): static
    {
        return $this->state(fn (array $attributes) => [
            'tipo_evento' => TipoEvento::RG,
            'lugar' => 'Sala de profesores',
        ]);
    }

    /**
     * Estado para evento tipo Reunión Derivación
     */
    public function reunionDerivacion(): static
    {
        return $this->state(fn (array $attributes) => [
            'tipo_evento' => TipoEvento::RD,
            'lugar' => fake()->randomElement(['Sala de reuniones', 'Despacho del director']),
        ]);
    }

    /**
     * Estado para evento tipo Cita Familiar
     */
    public function citaFamiliar(): static
    {
        return $this->state(fn (array $attributes) => [
            'tipo_evento' => TipoEvento::CITA_FAMILIAR,
            'lugar' => fake()->randomElement(['Sala de reuniones', 'Consultorio']),
        ]);
    }

    /**
     * Estado para evento tipo Derivación Externa
     */
    public function derivacionExterna(): static
    {
        return $this->state(fn (array $attributes) => [
            'tipo_evento' => TipoEvento::DERIVACION_EXTERNA,
            'lugar' => 'Consultorio externo',
            'notas' => 'Profesional externo: ' . fake()->name(),
        ]);
    }

    /**
     * Estado para evento en el pasado
     */
    public function pasado(): static
    {
        return $this->state(fn (array $attributes) => [
            'fecha_hora' => fake()->dateTimeBetween('-2 months', '-1 day'),
        ]);
    }

    /**
     * Estado para evento futuro
     */
    public function futuro(): static
    {
        return $this->state(fn (array $attributes) => [
            'fecha_hora' => fake()->dateTimeBetween('+1 day', '+3 months'),
        ]);
    }
}
