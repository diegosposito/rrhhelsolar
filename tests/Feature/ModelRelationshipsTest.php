<?php

use App\Models\ObraSocial;
use App\Models\Paciente;
use App\Models\Personal;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('relates Paciente to ObraSocial and back', function () {
    $obra = ObraSocial::factory()->create();
    $paciente = Paciente::factory()->create(['obra_social_id' => $obra->id]);

    expect($paciente->obraSocial->id)->toBe($obra->id)
        ->and($obra->pacientes->pluck('id'))->toContain($paciente->id);
});

it('relates User to Role and back', function () {
    $role = Role::factory()->create();
    $user = User::factory()->create(['role_id' => $role->id]);

    expect($user->role->id)->toBe($role->id)
        ->and($role->users->pluck('id'))->toContain($user->id);
});

it('exposes an activoPorDni scope that only returns active personal', function () {
    $activo = Personal::factory()->create(['dni' => '11111111', 'activo' => true]);
    Personal::factory()->create(['dni' => '22222222', 'activo' => false]);

    expect(Personal::activoPorDni('11111111')->first()?->id)->toBe($activo->id)
        ->and(Personal::activoPorDni('22222222')->first())->toBeNull();
});
