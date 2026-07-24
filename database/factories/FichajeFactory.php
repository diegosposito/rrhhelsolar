<?php

namespace Database\Factories;

use App\Enums\TipoFichaje;
use App\Models\Fichaje;
use App\Models\Personal;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Fichaje>
 */
class FichajeFactory extends Factory
{
    protected $model = Fichaje::class;

    public function definition(): array
    {
        return [
            'personal_id' => Personal::factory(),
            'tipo' => TipoFichaje::Entrada,
            'contabiliza' => true,
            'fecha_hora' => now(),
            'observacion' => null,
        ];
    }

    public function salida(): static
    {
        return $this->state(fn (array $attributes) => ['tipo' => TipoFichaje::Salida]);
    }

    public function noContabiliza(): static
    {
        return $this->state(fn (array $attributes) => ['contabiliza' => false]);
    }
}
