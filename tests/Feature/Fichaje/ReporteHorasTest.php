<?php

use App\Domain\Fichaje\ReporteHoras;
use App\Enums\TipoFichaje;
use App\Models\Fichaje;
use App\Models\Personal;
use App\Support\Duracion;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function marcar(Personal $personal, TipoFichaje $tipo, string $fechaHora, ?string $observacion = null): void
{
    Fichaje::create([
        'personal_id' => $personal->id,
        'tipo' => $tipo,
        'fecha_hora' => Carbon::parse($fechaHora),
        'observacion' => $observacion,
    ]);
}

it('computes worked seconds for a single closed pair to the second', function () {
    $personal = Personal::factory()->create();

    marcar($personal, TipoFichaje::Entrada, '2026-07-10 08:05:30');
    marcar($personal, TipoFichaje::Salida, '2026-07-10 12:02:55');

    $segundos = app(ReporteHoras::class)->segundosTrabajados($personal, Carbon::parse('2026-07-10'));

    // 12:02:55 - 08:05:30 = 3h 57m 25s = 14245 seconds
    expect($segundos)->toBe(14245);
});

it('sums multiple pairs in the same day and ignores a trailing orphan entrada', function () {
    $personal = Personal::factory()->create();

    marcar($personal, TipoFichaje::Entrada, '2026-07-10 08:00:00');
    marcar($personal, TipoFichaje::Salida, '2026-07-10 12:00:00');
    marcar($personal, TipoFichaje::Entrada, '2026-07-10 14:00:00');
    marcar($personal, TipoFichaje::Salida, '2026-07-10 17:30:15');
    marcar($personal, TipoFichaje::Entrada, '2026-07-10 18:00:00'); // orphan, never closed

    $segundos = app(ReporteHoras::class)->segundosTrabajados($personal, Carbon::parse('2026-07-10'));

    // 4h (14400) + 3h30m15s (12615) = 27015; orphan ignored
    expect($segundos)->toBe(27015);
});

it('splits the month into quincenas and keeps the invariant mensual == 1ra + 2da', function () {
    $personal = Personal::factory()->create();

    // First fortnight (day 10): 8h
    marcar($personal, TipoFichaje::Entrada, '2026-07-10 08:00:00');
    marcar($personal, TipoFichaje::Salida, '2026-07-10 16:00:00');
    // Boundary day 15 belongs to the first fortnight: 2h
    marcar($personal, TipoFichaje::Entrada, '2026-07-15 09:00:00');
    marcar($personal, TipoFichaje::Salida, '2026-07-15 11:00:00');
    // Day 16 belongs to the second fortnight: 3h
    marcar($personal, TipoFichaje::Entrada, '2026-07-16 09:00:00');
    marcar($personal, TipoFichaje::Salida, '2026-07-16 12:00:00');

    $resumen = app(ReporteHoras::class)->resumenMensual($personal, 7, 2026);

    expect($resumen['primeraQuincena'])->toBe(36000) // 8h + 2h
        ->and($resumen['segundaQuincena'])->toBe(10800) // 3h
        ->and($resumen['mensual'])->toBe(46800)
        ->and($resumen['mensual'])->toBe($resumen['primeraQuincena'] + $resumen['segundaQuincena']);
});

it('lists each day with movement including joined observations, skipping blanks', function () {
    $personal = Personal::factory()->create();

    marcar($personal, TipoFichaje::Entrada, '2026-07-10 08:00:00', 'Entra normal');
    marcar($personal, TipoFichaje::Salida, '2026-07-10 12:00:00', '   '); // blank -> skipped
    marcar($personal, TipoFichaje::Entrada, '2026-07-12 08:00:00', 'Llega tarde');
    marcar($personal, TipoFichaje::Salida, '2026-07-12 12:00:00', 'Sale temprano');

    $detalle = app(ReporteHoras::class)->detallePorDia($personal, 7, 2026);

    expect($detalle)->toHaveCount(2)
        ->and($detalle[0]['fecha']->toDateString())->toBe('2026-07-10')
        ->and($detalle[0]['segundos'])->toBe(14400)
        ->and($detalle[0]['observaciones'])->toBe('Entra normal')
        ->and($detalle[1]['observaciones'])->toBe('Llega tarde; Sale temprano');
});

it('returns pairs with an open trailing orphan marked as not closed', function () {
    $personal = Personal::factory()->create();

    marcar($personal, TipoFichaje::Entrada, '2026-07-10 08:00:00');
    marcar($personal, TipoFichaje::Salida, '2026-07-10 12:00:00');
    marcar($personal, TipoFichaje::Entrada, '2026-07-10 14:00:00'); // orphan

    $pares = app(ReporteHoras::class)->pares($personal, 7, 2026);

    expect($pares)->toHaveCount(2)
        ->and($pares[0]['cerrado'])->toBeTrue()
        ->and($pares[0]['egreso']->format('H:i:s'))->toBe('12:00:00')
        ->and($pares[1]['cerrado'])->toBeFalse()
        ->and($pares[1]['egreso'])->toBeNull()
        ->and($pares[1]['ingreso']->format('H:i:s'))->toBe('14:00:00');
});

it('never pairs across midnight in pares', function () {
    $personal = Personal::factory()->create();

    marcar($personal, TipoFichaje::Entrada, '2026-07-10 23:00:00'); // orphan that day
    marcar($personal, TipoFichaje::Entrada, '2026-07-11 08:00:00');
    marcar($personal, TipoFichaje::Salida, '2026-07-11 16:00:00');

    $pares = app(ReporteHoras::class)->pares($personal, 7, 2026);

    expect($pares)->toHaveCount(2)
        ->and($pares[0]['fecha']->toDateString())->toBe('2026-07-10')
        ->and($pares[0]['cerrado'])->toBeFalse()
        ->and($pares[1]['fecha']->toDateString())->toBe('2026-07-11')
        ->and($pares[1]['cerrado'])->toBeTrue();
});

it('returns only personal with fichajes that month, sorted by apellido then nombre', function () {
    $conMovimiento1 = Personal::factory()->create(['apellido' => 'Zapata', 'nombre' => 'Ana']);
    $conMovimiento2 = Personal::factory()->create(['apellido' => 'Alvarez', 'nombre' => 'Beto']);
    $sinMovimiento = Personal::factory()->create(['apellido' => 'Benitez', 'nombre' => 'Carla']);

    marcar($conMovimiento1, TipoFichaje::Entrada, '2026-07-10 08:00:00');
    marcar($conMovimiento1, TipoFichaje::Salida, '2026-07-10 12:00:00');
    marcar($conMovimiento2, TipoFichaje::Entrada, '2026-07-11 08:00:00');
    marcar($conMovimiento2, TipoFichaje::Salida, '2026-07-11 12:00:00');
    // Movement in a different month must not qualify
    marcar($sinMovimiento, TipoFichaje::Entrada, '2026-06-10 08:00:00');
    marcar($sinMovimiento, TipoFichaje::Salida, '2026-06-10 12:00:00');

    $empleados = app(ReporteHoras::class)->empleadosConMovimientos(7, 2026);

    expect($empleados)->toHaveCount(2)
        ->and($empleados->first()->apellido)->toBe('Alvarez')
        ->and($empleados->last()->apellido)->toBe('Zapata');
});

it('formats seconds as zero-padded HH:MM:SS with hours allowed to exceed 24', function () {
    expect(Duracion::hms(0))->toBe('00:00:00')
        ->and(Duracion::hms(3600))->toBe('01:00:00')
        ->and(Duracion::hms(222856))->toBe('61:54:16')
        ->and(Duracion::hms(14245))->toBe('03:57:25');
});
