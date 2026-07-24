<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Create a user bound to a role by name, with the given active state.
 */
function makeKioskUser(string $roleName, bool $activo): User
{
    $role = Role::factory()->create(['nombre' => $roleName]);

    return User::factory()->create([
        'role_id' => $role->id,
        'activo' => $activo,
    ]);
}

it('grants an active fichaje user access to the kiosk', function () {
    $user = makeKioskUser('fichaje', true);

    $this->actingAs($user)
        ->get('/registro')
        ->assertSuccessful();
});

it('grants an active admin access to the kiosk', function () {
    $user = makeKioskUser('admin', true);

    $this->actingAs($user)
        ->get('/registro')
        ->assertSuccessful();
});

it('redirects a guest to the login page', function () {
    $this->get('/registro')
        ->assertRedirect('/login');
});

it('denies an active user with an unrelated role', function () {
    $user = makeKioskUser('otro', true);

    $this->actingAs($user)
        ->get('/registro')
        ->assertForbidden();
});

it('logs out and redirects an inactive fichaje user', function () {
    $user = makeKioskUser('fichaje', false);

    $this->actingAs($user)
        ->get('/registro')
        ->assertRedirect('/login');

    $this->assertGuest();
});
