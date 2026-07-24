<?php

use App\Domain\Fichaje\FichajeInvalidoException;
use App\Domain\Fichaje\RegistrarFichaje;
use App\Enums\TipoFichaje;
use App\Models\Fichaje;
use App\Models\Personal;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    Carbon::setTestNow(Carbon::create(2026, 7, 24, 8, 0, 0));
    $this->service = app(RegistrarFichaje::class);
});

afterEach(function () {
    Carbon::setTestNow();
});

it('creates an entrada fichaje with tipo entrada and correct fecha_hora', function () {
    $personal = Personal::factory()->create();
    $momento = Carbon::create(2026, 7, 24, 8, 0, 0);

    $fichaje = $this->service->registrar($personal, TipoFichaje::Entrada, null, $momento);

    expect($fichaje)->toBeInstanceOf(Fichaje::class)
        ->and($fichaje->tipo)->toBe(TipoFichaje::Entrada)
        ->and($fichaje->fecha_hora->equalTo($momento))->toBeTrue()
        ->and($fichaje->personal_id)->toBe($personal->id);

    expect(Fichaje::count())->toBe(1);
});

it('throws when a second consecutive entrada is registered while an entrada is open', function () {
    $personal = Personal::factory()->create();

    $this->service->registrar($personal, TipoFichaje::Entrada, null, Carbon::create(2026, 7, 24, 8, 0));

    expect(fn () => $this->service->registrar($personal, TipoFichaje::Entrada, null, Carbon::create(2026, 7, 24, 9, 0)))
        ->toThrow(FichajeInvalidoException::class, 'Ya existe una entrada sin cerrar');

    expect(Fichaje::count())->toBe(1);
});

it('throws when a salida is registered with no open entrada', function () {
    $personal = Personal::factory()->create();

    expect(fn () => $this->service->registrar($personal, TipoFichaje::Salida, null, Carbon::create(2026, 7, 24, 17, 0)))
        ->toThrow(FichajeInvalidoException::class, 'No hay una entrada abierta para cerrar');

    expect(Fichaje::count())->toBe(0);
});

it('allows opening a second pair: entrada -> salida -> entrada', function () {
    $personal = Personal::factory()->create();

    $this->service->registrar($personal, TipoFichaje::Entrada, null, Carbon::create(2026, 7, 24, 8, 0));
    $this->service->registrar($personal, TipoFichaje::Salida, null, Carbon::create(2026, 7, 24, 12, 0));
    $tercero = $this->service->registrar($personal, TipoFichaje::Entrada, null, Carbon::create(2026, 7, 24, 13, 0));

    expect($tercero->tipo)->toBe(TipoFichaje::Entrada);
    expect(Fichaje::count())->toBe(3);
});

it('is day-bounded: an orphan entrada yesterday does not block a fresh entrada today', function () {
    $personal = Personal::factory()->create();

    // Orphan entrada at 23:00 yesterday, never closed.
    $this->service->registrar($personal, TipoFichaje::Entrada, null, Carbon::create(2026, 7, 23, 23, 0));

    // Fresh entrada today is allowed.
    $hoy = $this->service->registrar($personal, TipoFichaje::Entrada, null, Carbon::create(2026, 7, 24, 8, 0));

    expect($hoy->tipo)->toBe(TipoFichaje::Entrada);
    expect(Fichaje::count())->toBe(2);
});

it('does not allow a salida today paired against an open entrada from yesterday', function () {
    $personal = Personal::factory()->create();

    $this->service->registrar($personal, TipoFichaje::Entrada, null, Carbon::create(2026, 7, 23, 23, 0));

    // No open entrada TODAY, so a salida today must be rejected.
    expect(fn () => $this->service->registrar($personal, TipoFichaje::Salida, null, Carbon::create(2026, 7, 24, 6, 0)))
        ->toThrow(FichajeInvalidoException::class, 'No hay una entrada abierta para cerrar');
});

it('rejects registration for a non-existent DNI and creates no fichaje', function () {
    expect(fn () => $this->service->registrarPorDni('99999999', TipoFichaje::Entrada))
        ->toThrow(FichajeInvalidoException::class);

    expect(Fichaje::count())->toBe(0);
});

it('rejects registration for an inactive personal by DNI and creates no fichaje', function () {
    Personal::factory()->create(['dni' => '12345678', 'activo' => false]);

    expect(fn () => $this->service->registrarPorDni('12345678', TipoFichaje::Entrada))
        ->toThrow(FichajeInvalidoException::class);

    expect(Fichaje::count())->toBe(0);
});

it('registers by DNI for an active personal', function () {
    $personal = Personal::factory()->create(['dni' => '87654321', 'activo' => true]);

    $fichaje = $this->service->registrarPorDni('87654321', TipoFichaje::Entrada);

    expect($fichaje->personal_id)->toBe($personal->id)
        ->and($fichaje->tipo)->toBe(TipoFichaje::Entrada);
});
