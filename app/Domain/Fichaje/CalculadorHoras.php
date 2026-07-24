<?php

namespace App\Domain\Fichaje;

use App\Enums\TipoFichaje;
use App\Models\Personal;
use Carbon\CarbonInterface;
use Carbon\CarbonPeriod;

/**
 * Calculates worked minutes for a personal by pairing consecutive
 * entrada -> salida punches within each day. A trailing orphan entrada
 * (no matching salida on the same day) is ignored.
 */
class CalculadorHoras
{
    /**
     * Worked minutes for a single day.
     */
    public function minutosTrabajados(Personal $personal, CarbonInterface $fecha): int
    {
        $fichajes = $personal->fichajes()
            ->whereDate('fecha_hora', $fecha->toDateString())
            ->orderBy('fecha_hora')
            ->orderBy('id')
            ->get();

        $minutos = 0;
        $entradaAbierta = null;

        foreach ($fichajes as $fichaje) {
            if ($fichaje->tipo === TipoFichaje::Entrada) {
                // A new entrada opens a pair; any previous unpaired entrada is
                // discarded as an orphan (alternation guarantees this won't
                // normally happen, but the calculator stays robust).
                $entradaAbierta = $fichaje->fecha_hora;

                continue;
            }

            if ($entradaAbierta !== null) {
                $minutos += $entradaAbierta->diffInMinutes($fichaje->fecha_hora);
                $entradaAbierta = null;
            }
        }

        return (int) $minutos;
    }

    /**
     * Worked minutes summed across an inclusive date range.
     */
    public function minutosTrabajadosEnRango(
        Personal $personal,
        CarbonInterface $desde,
        CarbonInterface $hasta,
    ): int {
        $total = 0;

        foreach (CarbonPeriod::create($desde->copy()->startOfDay(), $hasta->copy()->startOfDay()) as $dia) {
            $total += $this->minutosTrabajados($personal, $dia);
        }

        return $total;
    }
}
