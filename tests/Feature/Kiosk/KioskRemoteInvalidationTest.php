<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('logs out a kiosk user whose account is deactivated remotely', function () {
    $role = Role::factory()->create(['nombre' => 'fichaje']);
    $user = User::factory()->create(['role_id' => $role->id, 'activo' => true]);

    // First request while active succeeds.
    $this->actingAs($user)
        ->get('/registro')
        ->assertSuccessful();

    // An admin flips the account inactive out of band.
    $user->forceFill(['activo' => false])->save();

    // The next kiosk interaction must kill the session.
    $this->actingAs($user)
        ->get('/registro')
        ->assertRedirect('/login');

    $this->assertGuest();
});
