<?php

namespace Database\Factories;

use App\Models\Personal;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Personal>
 */
class PersonalFactory extends Factory
{
    protected $model = Personal::class;

    public function definition(): array
    {
        return [
            'nombre' => fake()->firstName(),
            'apellido' => fake()->lastName(),
            'dni' => (string) fake()->unique()->numberBetween(1_000_000, 9_999_999),
            'sexo' => fake()->optional()->randomElement(['M', 'F']),
            'fecha_nacimiento' => fake()->optional()->date(),
            'fecha_ingreso' => fake()->optional()->date(),
            'direccion' => fake()->optional()->streetAddress(),
            'ciudad' => fake()->optional()->city(),
            'telefono' => fake()->optional()->phoneNumber(),
            'celular' => fake()->optional()->phoneNumber(),
            'email' => fake()->optional()->safeEmail(),
            'area' => fake()->optional()->word(),
            'horario_semanal' => fake()->optional()->sentence(),
            'observaciones' => fake()->optional()->sentence(),
            'activo' => true,
        ];
    }

    public function inactivo(): static
    {
        return $this->state(fn (array $attributes) => ['activo' => false]);
    }
}
