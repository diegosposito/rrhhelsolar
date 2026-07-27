<?php

namespace App\Domain\Fichaje;

use App\Models\PeriodoQuincena;
use Carbon\Carbon;

/**
 * Resolves the primera/segunda quincena date ranges for a given month/year.
 *
 * If an explicit PeriodoQuincena configuration exists it wins; otherwise the
 * report falls back to the historical default (day 1-15 and 16-end), so periods
 * that were never configured (e.g. migrated legacy months) keep their behaviour.
 *
 * Ranges are always full-coverage and contiguous: the two together tile the
 * whole calendar month with no gaps or overlaps.
 */
class Quincenas
{
    private const CORTE_POR_DEFECTO = 15;

    /**
     * @return array{primera: array{0: Carbon, 1: Carbon}, segunda: array{0: Carbon, 1: Carbon}}
     */
    public function rangos(int $mes, int $anio): array
    {
        $periodo = PeriodoQuincena::query()
            ->where('anio', $anio)
            ->where('mes', $mes)
            ->first();

        if ($periodo !== null) {
            return [
                'primera' => [$periodo->primera_inicio->copy()->startOfDay(), $periodo->primera_fin->copy()->startOfDay()],
                'segunda' => [$periodo->segunda_inicio->copy()->startOfDay(), $periodo->segunda_fin->copy()->startOfDay()],
            ];
        }

        return $this->derivarRangos($anio, $mes, self::CORTE_POR_DEFECTO);
    }

    /**
     * Human labels for the period's fortnight ranges, e.g. "01/07/2026 al
     * 17/07/2026". Built from the same resolved ranges the totals use, so an
     * unconfigured period shows its 1-15 / 16-end fallback.
     *
     * @return array{primera: string, segunda: string}
     */
    public function etiquetas(int $mes, int $anio): array
    {
        $rangos = $this->rangos($mes, $anio);

        return [
            'primera' => $this->etiqueta($rangos['primera']),
            'segunda' => $this->etiqueta($rangos['segunda']),
        ];
    }

    /**
     * Whether the period can be configured: only the current month/year.
     */
    public function esEditable(int $mes, int $anio): bool
    {
        $ahora = Carbon::now();

        return $ahora->year === $anio && $ahora->month === $mes;
    }

    /**
     * Cut day (last day of the first fortnight) to pre-fill a new period with.
     * Uses the most recently configured earlier period, else the default (15).
     */
    public function corteSugerido(int $mes, int $anio): int
    {
        $anterior = PeriodoQuincena::query()
            ->where(function ($q) use ($mes, $anio) {
                $q->where('anio', '<', $anio)
                    ->orWhere(fn ($q2) => $q2->where('anio', $anio)->where('mes', '<', $mes));
            })
            ->orderByDesc('anio')
            ->orderByDesc('mes')
            ->first();

        $corte = $anterior !== null ? $anterior->primera_fin->day : self::CORTE_POR_DEFECTO;

        // Clamp so the second fortnight always keeps at least one day.
        return max(1, min($corte, Carbon::create($anio, $mes, 1)->daysInMonth - 1));
    }

    /**
     * Build the four full-coverage dates for a period given the cut day.
     *
     * @return array{primera_inicio: string, primera_fin: string, segunda_inicio: string, segunda_fin: string}
     */
    public function derivarFechas(int $anio, int $mes, int $corteDia): array
    {
        $inicio = Carbon::create($anio, $mes, 1);
        $maxCorte = $inicio->copy()->daysInMonth - 1;
        $corteDia = max(1, min($corteDia, $maxCorte));
        $corte = Carbon::create($anio, $mes, $corteDia);

        return [
            'primera_inicio' => $inicio->toDateString(),
            'primera_fin' => $corte->toDateString(),
            'segunda_inicio' => $corte->copy()->addDay()->toDateString(),
            'segunda_fin' => $inicio->copy()->endOfMonth()->toDateString(),
        ];
    }

    /**
     * @param  array{0: Carbon, 1: Carbon}  $rango
     */
    private function etiqueta(array $rango): string
    {
        return $rango[0]->format('d/m/Y').' al '.$rango[1]->format('d/m/Y');
    }

    /**
     * @return array{primera: array{0: Carbon, 1: Carbon}, segunda: array{0: Carbon, 1: Carbon}}
     */
    private function derivarRangos(int $anio, int $mes, int $corteDia): array
    {
        $f = $this->derivarFechas($anio, $mes, $corteDia);

        return [
            'primera' => [Carbon::parse($f['primera_inicio']), Carbon::parse($f['primera_fin'])],
            'segunda' => [Carbon::parse($f['segunda_inicio']), Carbon::parse($f['segunda_fin'])],
        ];
    }
}
