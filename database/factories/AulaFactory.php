<?php
namespace Database\Factories;

use App\Models\Aula;
use Illuminate\Database\Eloquent\Factories\Factory;

class AulaFactory extends Factory
{
    protected $model = Aula::class;

    public function definition(): array
    {
        do {
            $curso = $this->faker->numberBetween(1, 6);
            $division = $this->faker->randomElement(['A', 'B', 'C', 'D', 'E']);
        } while (Aula::where('curso', $curso)->where('division', $division)->exists());

        return [
            'curso' => $curso,
            'division' => $division,
        ];
    }
}
