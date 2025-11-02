<?php
namespace Database\Factories;
use App\Models\Persona;
use Illuminate\Database\Eloquent\Factories\Factory;

class PersonaFactory extends Factory
{
    protected $model = Persona::class;

    public function definition(): array
    {
        return [
            'nombre' => $this->faker->firstName(),
            'apellido' => $this->faker->lastName(),
            'dni' => $this->faker->unique()->numberBetween(1000000, 45000000),
            'fecha_nacimiento' => $this->faker->date(),
            'domicilio' => $this->faker->address(),
            'nacionalidad' => $this->faker->country(),
            'activo' => $this->faker->boolean(90),
        ];
    }
} 