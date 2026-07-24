<?php

namespace Database\Factories;

use App\Models\ObraSocial;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ObraSocial>
 */
class ObraSocialFactory extends Factory
{
    protected $model = ObraSocial::class;

    public function definition(): array
    {
        return [
            'denominacion' => fake()->unique()->company(),
            'abreviada' => fake()->optional()->lexify('???'),
        ];
    }
}
