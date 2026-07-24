<?php

use App\Domain\Fichaje\Quincenas;
use App\Domain\Fichaje\ReporteHoras;
use App\Enums\TipoFichaje;
use App\Models\Fichaje;
use App\Models\PeriodoQuincena;
use App\Models\Personal;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function ficharPar(Personal $personal, string $dia, string $desde, string $hasta): void
{
    Fichaje::create([
        'personal_id' => $personal->id,
        'tipo' => TipoFichaje::Entrada,
        'fecha_hora' => Carbon::parse("$dia $desde"),
    ]);
    Fichaje::create([
        'personal_id' => $personal->id,
        'tipo' => TipoFichaje::Salida,
        'fecha_hora' => Carbon::parse("$dia $hasta"),
    ]);
}

it('splits the month using the configured cut day, not the default 15', function () {
    $personal = Personal::factory()->create();
    PeriodoQuincena::factory()->periodo(2026, 8, 17)->create();

    // Day 17 -> first fortnight (cut is 17); day 18 -> second fortnight.
    ficharPar($personal, '2026-08-17', '08:00:00', '10:00:00'); // 2h
    ficharPar($personal, '2026-08-18', '08:00:00', '11:00:00'); // 3h

    $resumen = app(ReporteHoras::class)->resumenMensual($personal, 8, 2026);

    expect($resumen['primeraQuincena'])->toBe(7200)   // day 17 counted in 1ra
        ->and($resumen['segundaQuincena'])->toBe(10800) // day 18 in 2da
        ->and($resumen['mensual'])->toBe(18000)
        ->and($resumen['mensual'])->toBe($resumen['primeraQuincena'] + $resumen['segundaQuincena']);
});

it('falls back to the day-15 split when the period has no config', function () {
    $personal = Personal::factory()->create();

    ficharPar($personal, '2026-08-15', '08:00:00', '10:00:00'); // 2h -> 1ra
    ficharPar($personal, '2026-08-16', '08:00:00', '11:00:00'); // 3h -> 2da

    $resumen = app(ReporteHoras::class)->resumenMensual($personal, 8, 2026);

    expect($resumen['primeraQuincena'])->toBe(7200)
        ->and($resumen['segundaQuincena'])->toBe(10800);
});

it('suggests the previous configured period cut day, else 15', function () {
    $quincenas = app(Quincenas::class);

    expect($quincenas->corteSugerido(5, 2026))->toBe(15);

    PeriodoQuincena::factory()->periodo(2026, 8, 17)->create();

    // September takes August's cut; a far-future month takes the latest earlier one.
    expect($quincenas->corteSugerido(9, 2026))->toBe(17)
        ->and($quincenas->corteSugerido(1, 2030))->toBe(17);
});

it('derives full-coverage contiguous dates and clamps an out-of-range cut', function () {
    $quincenas = app(Quincenas::class);

    expect($quincenas->derivarFechas(2026, 8, 17))->toBe([
        'primera_inicio' => '2026-08-01',
        'primera_fin' => '2026-08-17',
        'segunda_inicio' => '2026-08-18',
        'segunda_fin' => '2026-08-31',
    ]);

    // Cut 31 in a 31-day month is clamped so the second fortnight keeps a day.
    expect($quincenas->derivarFechas(2026, 8, 31))->toBe([
        'primera_inicio' => '2026-08-01',
        'primera_fin' => '2026-08-30',
        'segunda_inicio' => '2026-08-31',
        'segunda_fin' => '2026-08-31',
    ]);
});

it('only allows configuring the current period, not past nor future', function () {
    Carbon::setTestNow('2026-07-24 12:00:00');
    $quincenas = app(Quincenas::class);

    expect($quincenas->esEditable(7, 2026))->toBeTrue()    // current month
        ->and($quincenas->esEditable(8, 2026))->toBeFalse() // future month -> blocked
        ->and($quincenas->esEditable(6, 2026))->toBeFalse() // past month -> blocked
        ->and($quincenas->esEditable(7, 2027))->toBeFalse(); // future year -> blocked

    expect(PeriodoQuincena::factory()->periodo(2026, 7, 15)->make()->esEditable())->toBeTrue()
        ->and(PeriodoQuincena::factory()->periodo(2026, 8, 15)->make()->esEditable())->toBeFalse()
        ->and(PeriodoQuincena::factory()->periodo(2026, 6, 15)->make()->esEditable())->toBeFalse();
});
