<?php

use App\Filament\Resources\PersonalResource\Pages\CreatePersonal;
use App\Models\Personal;
use App\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $role = Role::factory()->create(['nombre' => 'admin']);
    $admin = User::factory()->create(['role_id' => $role->id, 'activo' => true]);

    $this->actingAs($admin);
    Filament::setCurrentPanel(Filament::getPanel('admin'));
});

it('rejects a duplicate DNI on the Personal create form', function () {
    Personal::factory()->create(['dni' => '55555555']);

    Livewire::test(CreatePersonal::class)
        ->fillForm([
            'nombre' => 'Ana',
            'apellido' => 'García',
            'dni' => '55555555',
        ])
        ->call('create')
        ->assertHasFormErrors(['dni' => 'unique']);
});

it('accepts a unique DNI on the Personal create form', function () {
    Livewire::test(CreatePersonal::class)
        ->fillForm([
            'nombre' => 'Ana',
            'apellido' => 'García',
            'dni' => '66666666',
        ])
        ->call('create')
        ->assertHasNoFormErrors();
});
