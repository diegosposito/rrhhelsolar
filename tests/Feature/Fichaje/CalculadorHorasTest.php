<?php

use App\Domain\Fichaje\CalculadorHoras;
use App\Enums\TipoFichaje;
use App\Models\Fichaje;
use App\Models\Personal;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function punch(Personal $personal, TipoFichaje $tipo, string $fechaHora): void
{
    Fichaje::create([
        'personal_id' => $personal->id,
        'tipo' => $tipo,
        'fecha_hora' => Carbon::parse($fechaHora),
    ]);
}

it('sums multiple entrada/salida pairs in the same day', function () {
    $personal = Personal::factory()->create();

    punch($personal, TipoFichaje::Entrada, '2026-07-24 08:00');
    punch($personal, TipoFichaje::Salida, '2026-07-24 12:00');
    punch($personal, TipoFichaje::Entrada, '2026-07-24 13:00');
    punch($personal, TipoFichaje::Salida, '2026-07-24 17:00');

    $minutos = app(CalculadorHoras::class)->minutosTrabajados($personal, Carbon::parse('2026-07-24'));

    // 4h + 4h = 8h = 480 minutes
    expect($minutos)->toBe(480);
});

it('ignores a trailing orphan entrada in worked hours', function () {
    $personal = Personal::factory()->create();

    punch($personal, TipoFichaje::Entrada, '2026-07-24 08:00');
    punch($personal, TipoFichaje::Salida, '2026-07-24 12:00');
    punch($personal, TipoFichaje::Entrada, '2026-07-24 13:00'); // orphan, never closed

    $minutos = app(CalculadorHoras::class)->minutosTrabajados($personal, Carbon::parse('2026-07-24'));

    // Only the first 4h pair counts
    expect($minutos)->toBe(240);
});

it('does not pair an entrada across midnight', function () {
    $personal = Personal::factory()->create();

    // Orphan entrada yesterday at 23:00
    punch($personal, TipoFichaje::Entrada, '2026-07-23 23:00');
    // Today's proper pair
    punch($personal, TipoFichaje::Entrada, '2026-07-24 08:00');
    punch($personal, TipoFichaje::Salida, '2026-07-24 16:00');

    $ayer = app(CalculadorHoras::class)->minutosTrabajados($personal, Carbon::parse('2026-07-23'));
    $hoy = app(CalculadorHoras::class)->minutosTrabajados($personal, Carbon::parse('2026-07-24'));

    expect($ayer)->toBe(0); // orphan, not paired into today
    expect($hoy)->toBe(480); // 8h
});

it('returns zero minutes when there are no fichajes for the day', function () {
    $personal = Personal::factory()->create();

    $minutos = app(CalculadorHoras::class)->minutosTrabajados($personal, Carbon::parse('2026-07-24'));

    expect($minutos)->toBe(0);
});
