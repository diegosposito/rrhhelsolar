<?php

namespace App\Filament\Resources\PeriodoQuincenaResource\Pages;

use App\Domain\Fichaje\Quincenas;
use Carbon\Carbon;

/**
 * Rebuilds the four full-coverage dates from (año, mes, corte) at save time, so
 * what gets persisted is always a contiguous, whole-month split — independent of
 * the live form state.
 */
trait DerivaFechasQuincena
{
    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function derivarFechas(array $data): array
    {
        $anio = (int) $data['anio'];
        $mes = (int) $data['mes'];
        $corteDia = Carbon::parse($data['primera_fin'])->day;

        return array_merge(
            ['anio' => $anio, 'mes' => $mes],
            app(Quincenas::class)->derivarFechas($anio, $mes, $corteDia),
        );
    }
}
