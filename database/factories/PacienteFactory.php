<?php

namespace Database\Factories;

use App\Models\Paciente;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Paciente>
 */
class PacienteFactory extends Factory
{
    protected $model = Paciente::class;

    public function definition(): array
    {
        return [
            'nombre' => fake()->firstName(),
            'apellido' => fake()->lastName(),
            'direccion' => fake()->optional()->streetAddress(),
            'ciudad' => fake()->optional()->city(),
            'celular' => fake()->optional()->phoneNumber(),
            'email' => fake()->optional()->safeEmail(),
            'observaciones' => fake()->optional()->sentence(),
            'obra_social_id' => null,
        ];
    }
}
