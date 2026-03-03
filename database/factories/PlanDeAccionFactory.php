<?php
namespace Database\Factories;
use App\Models\PlanDeAccion;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Enums\TipoPlan;
use App\Enums\EstadoPlan;
use App\Models\Profesional;
use App\Models\Alumno;
use App\Models\Aula;

class PlanDeAccionFactory extends Factory
{
    protected $model = PlanDeAccion::class;

    public function definition(): array
    {
        $esActivo = $this->faker->boolean();
        return [
            'activo' => $esActivo,
            'estado_plan' => $esActivo ? EstadoPlan::ABIERTO->value : EstadoPlan::CERRADO->value,
            'tipo_plan' => $this->faker->randomElement(TipoPlan::cases()),
            'objetivos' => $this->faker->paragraph(),
            'acciones' => $this->faker->paragraphs(2, true),
            'observaciones' => $this->faker->paragraph(),
            'fk_id_profesional_generador' => Profesional::inRandomOrder()->first()->id_profesional,
        ];
    }

    public function individual(): Factory
    {
        return $this->state(fn (array $attributes) => [
            'tipo_plan' => TipoPlan::INDIVIDUAL->value,
        ])->afterCreating(function (PlanDeAccion $plan) {
            //Asegura que tenga 1 alumno
            $alumno = Alumno::inRandomOrder()->first() ?? Alumno::factory()->create();
            $plan->alumnos()->attach($alumno->id_alumno);
            
            $plan->profesionalesParticipantes()->attach($plan->fk_id_profesional_generador);
        });
    }

    public function grupal(): Factory
    {
        return $this->state(fn (array $attributes) => [
            'tipo_plan' => TipoPlan::GRUPAL->value,
        ])->afterCreating(function (PlanDeAccion $plan) {
            $aulas = Aula::inRandomOrder()->take($this->faker->numberBetween(1, 2))->get();
            $plan->aulas()->attach($aulas->pluck('id_aula'));
            
            $alumnos = Alumno::inRandomOrder()->take($this->faker->numberBetween(5, 10))->get();
            $plan->alumnos()->attach($alumnos->pluck('id_alumno'));
            
            $profesional = Profesional::inRandomOrder()->where('id_profesional', '!=', $plan->fk_id_profesional_generador)->first();
            if ($profesional) {
                $plan->profesionalesParticipantes()->attach($profesional->id_profesional);
            }
        });
    }

    public function institucional(): Factory
    {
        return $this->state(fn (array $attributes) => [
            'tipo_plan' => TipoPlan::INSTITUCIONAL->value,
        ])->afterCreating(function (PlanDeAccion $plan) {
            $profesionales = Profesional::inRandomOrder()->take($this->faker->numberBetween(1, 3))->get();
            $plan->profesionalesParticipantes()->attach($profesionales->pluck('id_profesional'));
        });
    }
} 