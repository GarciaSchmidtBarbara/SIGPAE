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

    public function withAlumnos(int $cantidad = 3)
    {
        return $this->afterCreating(function (PlanDeAccion $plan) use ($cantidad) {
            $alumnos = Alumno::factory()->count($cantidad)->create();
            $plan->alumnos()->attach($alumnos->pluck('id_alumno')); 
        });
    }

    public function withAulas(int $cantidad = 2)
    {
        return $this->afterCreating(function (PlanDeAccion $plan) use ($cantidad) {
            $aulas = Aula::factory()->count($cantidad)->create();
            $plan->aulas()->attach($aulas->pluck('id_aula'));
        });
    }
} 