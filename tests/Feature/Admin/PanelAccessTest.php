<?php

use App\Filament\Resources\PacienteResource;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Create a user bound to a role by name, with the given active state.
 */
function makeUserWithRole(string $roleName, bool $activo): User
{
    $role = Role::factory()->create(['nombre' => $roleName]);

    return User::factory()->create([
        'role_id' => $role->id,
        'activo' => $activo,
    ]);
}

it('grants an active admin access to the panel dashboard', function () {
    $admin = makeUserWithRole('admin', true);

    $this->actingAs($admin)
        ->get('/admin')
        ->assertSuccessful();
});

it('grants an active admin access to a resource index', function () {
    $admin = makeUserWithRole('admin', true);

    $this->actingAs($admin)
        ->get(PacienteResource::getUrl('index'))
        ->assertSuccessful();
});

it('denies a fichaje user access to the panel', function () {
    $fichaje = makeUserWithRole('fichaje', true);

    $this->actingAs($fichaje)
        ->get('/admin')
        ->assertForbidden();
});

it('denies an inactive admin access to the panel', function () {
    $inactiveAdmin = makeUserWithRole('admin', false);

    $this->actingAs($inactiveAdmin)
        ->get('/admin')
        ->assertForbidden();
});

it('redirects a guest to the login page', function () {
    $this->get('/admin')
        ->assertRedirect('/admin/login');
});
