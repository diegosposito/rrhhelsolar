<?php

namespace App\Domain\Fichaje;

use App\Enums\TipoFichaje;
use App\Models\Fichaje;
use App\Models\Personal;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

/**
 * Second-precision worked-time reporting on top of the fichaje pairing rules.
 *
 * Punches are paired entrada -> salida within a single day (day-bounded, never
 * across midnight). A trailing entrada with no matching salida on the same day
 * is an "orphan": it is reported as an open pair (cerrado = false) but never
 * contributes worked time.
 */
class ReporteHoras
{
    /**
     * Worked seconds for a single day (sum of closed pairs, to the second).
     */
    public function segundosTrabajados(Personal $personal, CarbonInterface $dia): int
    {
        $fichajes = $personal->fichajes()
            ->whereDate('fecha_hora', $dia->toDateString())
            ->orderBy('fecha_hora')
            ->orderBy('id')
            ->get();

        return $this->segundosDePares($this->paresDelDia($fichajes));
    }

    /**
     * Monthly worked seconds split into fortnights.
     *
     * Days 1-15 are the first fortnight; day 16 to the end of month is the
     * second. Invariant: mensual === primeraQuincena + segundaQuincena.
     *
     * @return array{mensual: int, primeraQuincena: int, segundaQuincena: int}
     */
    public function resumenMensual(Personal $personal, int $mes, int $anio): array
    {
        $primera = 0;
        $segunda = 0;

        foreach ($this->fichajesDelMesPorDia($personal, $mes, $anio) as $fecha => $fichajesDia) {
            $segundos = $this->segundosDePares($this->paresDelDia($fichajesDia));

            if (Carbon::parse($fecha)->day <= 15) {
                $primera += $segundos;
            } else {
                $segunda += $segundos;
            }
        }

        return [
            'mensual' => $primera + $segunda,
            'primeraQuincena' => $primera,
            'segundaQuincena' => $segunda,
        ];
    }

    /**
     * Per-day breakdown for every day of the month that has movement.
     *
     * Observations are the day's non-blank fichaje observations joined by "; ".
     *
     * @return list<array{fecha: Carbon, segundos: int, observaciones: string}>
     */
    public function detallePorDia(Personal $personal, int $mes, int $anio): array
    {
        $detalle = [];

        foreach ($this->fichajesDelMesPorDia($personal, $mes, $anio) as $fecha => $fichajesDia) {
            $segundos = $this->segundosDePares($this->paresDelDia($fichajesDia));

            // Only days with counted worked time appear in the breakdown, like
            // the legacy "Horas Trabajadas" table. Uncounted movement still shows
            // in the entrada/salida detail below it.
            if ($segundos === 0) {
                continue;
            }

            $observaciones = $fichajesDia
                ->map(fn (Fichaje $f): string => trim((string) $f->observacion))
                ->filter(fn (string $obs): bool => $obs !== '')
                ->implode('; ');

            $detalle[] = [
                'fecha' => Carbon::parse($fecha),
                'segundos' => $segundos,
                'observaciones' => $observaciones,
            ];
        }

        return $detalle;
    }

    /**
     * Ordered entrada -> salida pairs across the month. A trailing orphan
     * entrada has egreso = null and cerrado = false (the red/open state).
     *
     * @return list<array{fecha: Carbon, ingreso: Carbon, egreso: ?Carbon, cerrado: bool}>
     */
    public function pares(Personal $personal, int $mes, int $anio): array
    {
        $pares = [];

        foreach ($this->fichajesDelMesPorDia($personal, $mes, $anio) as $fecha => $fichajesDia) {
            foreach ($this->paresDelDia($fichajesDia) as $par) {
                $pares[] = [
                    'fecha' => Carbon::parse($fecha),
                    'ingreso' => $par['ingreso'],
                    'egreso' => $par['egreso'],
                    'cerrado' => $par['cerrado'],
                ];
            }
        }

        return $pares;
    }

    /**
     * Personal with at least one fichaje in the given month, ordered by
     * apellido then nombre.
     *
     * @return Collection<int, Personal>
     */
    public function empleadosConMovimientos(int $mes, int $anio): Collection
    {
        [$inicio, $fin] = $this->limitesDelMes($mes, $anio);

        return Personal::query()
            ->whereHas('fichajes', fn ($query) => $query->whereBetween('fecha_hora', [$inicio, $fin]))
            ->orderBy('apellido')
            ->orderBy('nombre')
            ->get();
    }

    /**
     * Month fichajes grouped by Y-m-d, ordered chronologically.
     *
     * @return Collection<string, Collection<int, Fichaje>>
     */
    private function fichajesDelMesPorDia(Personal $personal, int $mes, int $anio): Collection
    {
        [$inicio, $fin] = $this->limitesDelMes($mes, $anio);

        return $personal->fichajes()
            ->whereBetween('fecha_hora', [$inicio, $fin])
            ->orderBy('fecha_hora')
            ->orderBy('id')
            ->get()
            ->groupBy(fn (Fichaje $f): string => $f->fecha_hora->toDateString());
    }

    /**
     * Pair a single day's ordered fichajes into entrada -> salida pairs.
     *
     * @param  Collection<int, Fichaje>  $fichajesDia
     * @return list<array{ingreso: Carbon, egreso: ?Carbon, cerrado: bool}>
     */
    private function paresDelDia(Collection $fichajesDia): array
    {
        $pares = [];
        $entradaAbierta = null;

        foreach ($fichajesDia as $fichaje) {
            // Punches not flagged for control never contribute worked time; they
            // are surfaced as an invalid/open row so the detail still shows them
            // (matches the legacy report, where controlar=0 punches appear red).
            if (! $fichaje->contabiliza) {
                $pares[] = ['ingreso' => $fichaje->fecha_hora, 'egreso' => null, 'cerrado' => false];

                continue;
            }

            if ($fichaje->tipo === TipoFichaje::Entrada) {
                // A previous unpaired entrada becomes an orphan open pair.
                if ($entradaAbierta !== null) {
                    $pares[] = ['ingreso' => $entradaAbierta, 'egreso' => null, 'cerrado' => false];
                }

                $entradaAbierta = $fichaje->fecha_hora;

                continue;
            }

            // salida
            if ($entradaAbierta !== null) {
                $pares[] = ['ingreso' => $entradaAbierta, 'egreso' => $fichaje->fecha_hora, 'cerrado' => true];
                $entradaAbierta = null;
            }
            // A salida with no open entrada is ignored.
        }

        if ($entradaAbierta !== null) {
            $pares[] = ['ingreso' => $entradaAbierta, 'egreso' => null, 'cerrado' => false];
        }

        return $pares;
    }

    /**
     * Sum worked seconds across closed pairs only.
     *
     * @param  list<array{ingreso: Carbon, egreso: ?Carbon, cerrado: bool}>  $pares
     */
    private function segundosDePares(array $pares): int
    {
        $segundos = 0;

        foreach ($pares as $par) {
            if ($par['cerrado'] && $par['egreso'] !== null) {
                $segundos += (int) abs($par['ingreso']->diffInSeconds($par['egreso']));
            }
        }

        return $segundos;
    }

    /**
     * Inclusive start/end datetimes for a month.
     *
     * @return array{0: Carbon, 1: Carbon}
     */
    private function limitesDelMes(int $mes, int $anio): array
    {
        $inicio = Carbon::create($anio, $mes, 1)->startOfMonth();
        $fin = $inicio->copy()->endOfMonth();

        return [$inicio, $fin];
    }
}
