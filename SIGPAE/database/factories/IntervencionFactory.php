<?php
namespace Database\Factories;

use App\Models\Intervencion;
use App\Models\Profesional;
use App\Models\PlanDeAccion;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;

class IntervencionFactory extends Factory
{
    protected $model = Intervencion::class;

    public function definition()
    {
        $modalidades = ['PRESENCIAL', 'ONLINE', 'OTRA'];
        $tiposIntervencion = ['ESPONTANEA', 'PROGRAMADA'];

        $tipo = $this->faker->randomElement($tiposIntervencion);

        return [
            'fecha_hora_intervencion' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'lugar' => $this->faker->city(),
            'modalidad' => $this->faker->randomElement($modalidades),
            'otra_modalidad' => $this->faker->optional()->word(),
            'temas_tratados' => $this->faker->paragraph(),
            'compromisos' => $this->faker->paragraph(),
            'observaciones' => $this->faker->optional()->sentence(),
            'activo' => $this->faker->boolean(90),
            'tipo_intervencion' => $tipo,
            'fk_id_profesional_genera' => Profesional::inRandomOrder()->first()->id_profesional,
            'fk_id_plan_de_accion' => $tipo === 'PROGRAMADA'
                ? PlanDeAccion::inRandomOrder()->first()->id_plan_de_accion
                : null,
            'fk_id_evaluacion_intervencion_espontanea' => null,
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Intervencion $intervencion) {
            // Asignar profesionales participantes (tabla intermedia reune)
            $profesionales = Profesional::inRandomOrder()->take(rand(1, 3))->pluck('id_profesional');
            $intervencion->profesionales()->sync($profesionales);

            // Asignar alumnos (tabla intermedia intervencion_alumno)
            $alumnos = \App\Models\Alumno::inRandomOrder()->take(rand(1, 5))->pluck('id_alumno');
            $intervencion->alumnos()->sync($alumnos);

            // Asignar aulas (tabla intermedia intervencion_aula)
            $aulas = \App\Models\Aula::inRandomOrder()->take(rand(0, 2))->pluck('id_aula');
            $intervencion->aulas()->sync($aulas);
        });
    }
}