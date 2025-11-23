<?php

namespace Database\Factories;

use App\Models\Intervencion;
use App\Models\Profesional;
use App\Models\PlanDeAccion;
use App\Models\EvaluacionDeIntervecionEspontanea;
use Illuminate\Database\Eloquent\Factories\Factory;

class IntervencionFactory extends Factory
{
    protected $model = Intervencion::class;

    public function definition()
    {
        // Tipos de modalidad y tipo de intervencion
        $modalidades = ['PRESENCIAL', 'ONLINE', 'OTRA'];
        $tiposIntervencion = ['ESPONTANEA', 'PROGRAMADA'];

        return [
            'fecha_hora_intervencion' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'lugar' => $this->faker->city(),
            'modalidad' => $this->faker->randomElement($modalidades),
            'otra_modalidad' => $this->faker->optional()->word(),
            'temas_tratados' => $this->faker->paragraph(),
            'compromisos' => $this->faker->paragraph(),
            'observaciones' => $this->faker->optional()->sentence(),
            'activo' => $this->faker->boolean(90), // 90% true
            'tipo_intervencion' => $this->faker->randomElement($tiposIntervencion),
            'fk_id_profesional_genera' => Profesional::factory(),
            'fk_id_plan_de_accion' => PlanDeAccion::factory()->optional()->create()->id,
            
            //ASIGNAR EVALUACION CUANDO SE CREEN LAS EVALUACIONES
            'fk_id_evaluacion_intervencion_espontanea' => null,
        ];
    }
}
