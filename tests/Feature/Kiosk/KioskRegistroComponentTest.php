<?php

use App\Enums\TipoFichaje;
use App\Livewire\Kiosk\Registro;
use App\Models\Fichaje;
use App\Models\Personal;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Carbon::setTestNow(Carbon::create(2026, 7, 24, 8, 0, 0));

    $role = Role::factory()->create(['nombre' => 'fichaje']);
    $this->operator = User::factory()->create(['role_id' => $role->id, 'activo' => true]);
});

afterEach(function () {
    Carbon::setTestNow();
});

it('registers an entrada for a valid active DNI and shows success', function () {
    $personal = Personal::factory()->create(['dni' => '12345678', 'activo' => true]);

    Livewire::actingAs($this->operator)
        ->test(Registro::class)
        ->set('dni', '12345678')
        ->call('registrar', 'entrada')
        ->assertHasNoErrors()
        ->assertSee('Registro ingresado correctamente')
        ->assertSet('dni', '');

    $this->assertDatabaseHas('fichajes', [
        'personal_id' => $personal->id,
        'tipo' => TipoFichaje::Entrada->value,
    ]);
});

it('shows an error and creates no fichaje for an unknown DNI', function () {
    Livewire::actingAs($this->operator)
        ->test(Registro::class)
        ->set('dni', '00000000')
        ->call('registrar', 'entrada')
        ->assertSee('No existe personal activo con el DNI 00000000');

    expect(Fichaje::count())->toBe(0);
});

it('shows an error and creates no fichaje for an inactive DNI', function () {
    Personal::factory()->create(['dni' => '99999999', 'activo' => false]);

    Livewire::actingAs($this->operator)
        ->test(Registro::class)
        ->set('dni', '99999999')
        ->call('registrar', 'entrada')
        ->assertSee('No existe personal activo con el DNI 99999999');

    expect(Fichaje::count())->toBe(0);
});

it('surfaces the alternation rule when a second entrada is attempted', function () {
    $personal = Personal::factory()->create(['dni' => '55555555', 'activo' => true]);

    Livewire::actingAs($this->operator)
        ->test(Registro::class)
        ->set('dni', '55555555')
        ->call('registrar', 'entrada')
        ->set('dni', '55555555')
        ->call('registrar', 'entrada')
        ->assertSee('Ya existe una entrada sin cerrar');

    expect(Fichaje::where('personal_id', $personal->id)->count())->toBe(1);
});

it('appends digits and clears via the keypad', function () {
    Livewire::actingAs($this->operator)
        ->test(Registro::class)
        ->call('appendDigit', '1')
        ->call('appendDigit', '2')
        ->call('appendDigit', '3')
        ->assertSet('dni', '123')
        ->call('clear')
        ->assertSet('dni', '');
});

it('persists an observation added to a fichaje', function () {
    $personal = Personal::factory()->create(['dni' => '77777777', 'activo' => true]);
    $fichaje = Fichaje::factory()->create(['personal_id' => $personal->id]);

    Livewire::actingAs($this->operator)
        ->test(Registro::class)
        ->call('editarObservacion', $fichaje->id)
        ->set('observacionEdit', 'Llegó tarde por el transporte')
        ->call('guardarObservacion');

    $this->assertDatabaseHas('fichajes', [
        'id' => $fichaje->id,
        'observacion' => 'Llegó tarde por el transporte',
    ]);
});
