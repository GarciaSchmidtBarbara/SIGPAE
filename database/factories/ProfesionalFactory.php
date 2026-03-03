<?php
namespace Database\Factories;

use App\Models\Profesional;
use App\Models\Persona;
use App\Enums\Siglas;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProfesionalFactory extends Factory
{
    
    protected $model = Profesional::class;

    
    public function definition(): array
    {

        $nombreSimulado = $this->faker->firstName();
        $apellidoSimulado = $this->faker->lastName();
        $usuarioSimulado = strtolower(Str::slug($nombreSimulado . '.' . $apellidoSimulado));
        $siglaAleatoria = $this->faker->randomElement(Siglas::cases());

        return [
            'fk_id_persona' => Persona::factory(), 
            'telefono' => $this->faker->phoneNumber(),
            'usuario' => $usuarioSimulado,
            'email' => $usuarioSimulado . '@' . $this->faker->unique()->domainWord() . '.gob.ar',
            'siglas' => $siglaAleatoria,
            'profesion' => $siglaAleatoria->label(),
            'contrasenia' => 'password',
            'activo' => true,
        ];
    }

    public function inactivo(): static
    {
        return $this->state(fn (array $attributes) => [
            'activo' => false,
        ]);
    }
  
}