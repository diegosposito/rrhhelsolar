<?php

use App\Livewire\Auth\Login;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function makeLoginUser(string $roleName, bool $activo = true): User
{
    $role = Role::factory()->create(['nombre' => $roleName]);

    return User::factory()->create([
        'role_id' => $role->id,
        'activo' => $activo,
        'password' => Hash::make('secret123'),
    ]);
}

it('logs in an admin and redirects to the admin panel', function () {
    $user = makeLoginUser('admin');

    Livewire::test(Login::class)
        ->set('email', $user->email)
        ->set('password', 'secret123')
        ->call('authenticate')
        ->assertRedirect('/admin');

    $this->assertAuthenticatedAs($user);
});

it('logs in a fichaje user and redirects to the kiosk', function () {
    $user = makeLoginUser('fichaje');

    Livewire::test(Login::class)
        ->set('email', $user->email)
        ->set('password', 'secret123')
        ->call('authenticate')
        ->assertRedirect('/registro');

    $this->assertAuthenticatedAs($user);
});

it('rejects invalid credentials without authenticating', function () {
    $user = makeLoginUser('fichaje');

    Livewire::test(Login::class)
        ->set('email', $user->email)
        ->set('password', 'wrong-password')
        ->call('authenticate')
        ->assertHasErrors('email');

    $this->assertGuest();
});

it('rejects an inactive user at login', function () {
    $user = makeLoginUser('fichaje', activo: false);

    Livewire::test(Login::class)
        ->set('email', $user->email)
        ->set('password', 'secret123')
        ->call('authenticate')
        ->assertHasErrors('email');

    $this->assertGuest();
});
