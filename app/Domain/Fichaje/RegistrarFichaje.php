<?php

namespace App\Domain\Fichaje;

use App\Enums\TipoFichaje;
use App\Models\Fichaje;
use App\Models\Personal;
use Carbon\Carbon;
use Carbon\CarbonInterface;

/**
 * Domain service that registers time-clock punches (fichajes) while
 * enforcing the day-bounded alternation rules.
 */
class RegistrarFichaje
{
    /**
     * Register a punch for the given personal, enforcing alternation.
     *
     * @throws FichajeInvalidoException
     */
    public function registrar(
        Personal $personal,
        TipoFichaje $tipo,
        ?string $observacion = null,
        ?CarbonInterface $momento = null,
    ): Fichaje {
        $momento = $momento ? Carbon::instance(Carbon::parse($momento)) : Carbon::now();

        $this->assertAlternacion($personal, $tipo, $momento);

        return Fichaje::create([
            'personal_id' => $personal->id,
            'tipo' => $tipo,
            'fecha_hora' => $momento,
            'observacion' => $observacion,
        ]);
    }

    /**
     * Resolve an active personal by DNI and register a punch for it.
     *
     * @throws FichajeInvalidoException
     */
    public function registrarPorDni(
        string $dni,
        TipoFichaje $tipo,
        ?string $observacion = null,
        ?CarbonInterface $momento = null,
    ): Fichaje {
        $personal = Personal::activoPorDni($dni)->first();

        if ($personal === null) {
            throw FichajeInvalidoException::personalNoEncontrado($dni);
        }

        return $this->registrar($personal, $tipo, $observacion, $momento);
    }

    /**
     * Enforce the day-bounded alternation rule for the punch being registered.
     *
     * @throws FichajeInvalidoException
     */
    private function assertAlternacion(Personal $personal, TipoFichaje $tipo, CarbonInterface $momento): void
    {
        $tieneEntradaAbierta = $this->tieneEntradaAbierta($personal, $momento);

        if ($tipo === TipoFichaje::Entrada && $tieneEntradaAbierta) {
            throw FichajeInvalidoException::entradaAbierta();
        }

        if ($tipo === TipoFichaje::Salida && ! $tieneEntradaAbierta) {
            throw FichajeInvalidoException::sinEntradaAbierta();
        }
    }

    /**
     * A person has an "open entrada" when their last punch on the same date
     * as $momento is an entrada. The check is scoped to that single day, so an
     * orphan entrada from a previous day is never considered open.
     */
    private function tieneEntradaAbierta(Personal $personal, CarbonInterface $momento): bool
    {
        $ultimo = $personal->fichajes()
            ->whereDate('fecha_hora', $momento->toDateString())
            ->orderByDesc('fecha_hora')
            ->orderByDesc('id')
            ->first();

        return $ultimo !== null && $ultimo->tipo === TipoFichaje::Entrada;
    }
}
