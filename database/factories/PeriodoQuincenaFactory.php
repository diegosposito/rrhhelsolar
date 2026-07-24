<?php

namespace Database\Factories;

use App\Models\PeriodoQuincena;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PeriodoQuincena>
 */
class PeriodoQuincenaFactory extends Factory
{
    protected $model = PeriodoQuincena::class;

    public function definition(): array
    {
        $anio = 2026;
        $mes = 8;

        return array_merge(
            ['anio' => $anio, 'mes' => $mes],
            $this->rangos($anio, $mes, 15),
        );
    }

    /**
     * Build a full-coverage config for the given period cut at $corteDia.
     */
    public function periodo(int $anio, int $mes, int $corteDia): static
    {
        return $this->state(fn (): array => array_merge(
            ['anio' => $anio, 'mes' => $mes],
            $this->rangos($anio, $mes, $corteDia),
        ));
    }

    /**
     * @return array{primera_inicio: string, primera_fin: string, segunda_inicio: string, segunda_fin: string}
     */
    private function rangos(int $anio, int $mes, int $corteDia): array
    {
        $inicio = Carbon::create($anio, $mes, 1);
        $corte = Carbon::create($anio, $mes, $corteDia);

        return [
            'primera_inicio' => $inicio->toDateString(),
            'primera_fin' => $corte->toDateString(),
            'segunda_inicio' => $corte->copy()->addDay()->toDateString(),
            'segunda_fin' => $inicio->copy()->endOfMonth()->toDateString(),
        ];
    }
}
