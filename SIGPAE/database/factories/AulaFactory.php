<?php
namespace Database\Factories;

use App\Models\Aula;
use Illuminate\Database\Eloquent\Factories\Factory;

class AulaFactory extends Factory
{
    protected $model = Aula::class;

    public function definition(): array
    {
        static $combinaciones = [];

        do {
            $curso = $this->faker->numberBetween(1, 6);
            $division = $this->faker->randomElement(['A', 'B', 'C', 'D', 'E']);
            $clave = "{$curso}-{$division}";
        } while (in_array($clave, $combinaciones));

        $combinaciones[] = $clave;

        return [
            'curso' => $curso,
            'division' => $division,
        ];
    }

}
