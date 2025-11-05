<?php
namespace Database\Factories;

use App\Models\Alumno;
use App\Models\Persona;
use App\Models\Aula;
use Illuminate\Database\Eloquent\Factories\Factory;

class AlumnoFactory extends Factory
{
    protected $model = Alumno::class;

    public function definition(): array
    {
        return [
            'fk_id_persona' => \App\Models\Persona::factory(),
            'fk_id_aula' => \App\Models\Aula::inRandomOrder()->first()->id_aula,
            'cud' => $this->faker->boolean(),
            'inasistencias' => $this->faker->numberBetween(0, 60),
            'observaciones' => $this->faker->sentence(),
            'antecedentes' => $this->faker->words(3, true),
            'intervenciones_externas' => $this->faker->sentence(),
            'actividades_extraescolares' => $this->faker->words(2, true),
            'situacion_escolar' => $this->faker->randomElement(['Con problemas de aprendizaje', 'Tiene acompañante', 'Din observaciones']),            
            'situacion_medica' => $this->faker->sentence(),
            'situacion_familiar' => $this->faker->sentence(),
            'situacion_socioeconomica' => $this->faker->sentence(),
        ];
    }
}
