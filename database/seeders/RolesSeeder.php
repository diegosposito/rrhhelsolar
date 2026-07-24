<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class RolesSeeder extends Seeder
{
    /**
     * Seed the two application roles and assign the admin user.
     */
    public function run(): void
    {
        $admin = Role::updateOrCreate(
            ['nombre' => 'admin'],
            ['descripcion' => 'Full administrative access'],
        );

        Role::updateOrCreate(
            ['nombre' => 'fichaje'],
            ['descripcion' => 'Time-clock kiosk access'],
        );

        // Assign the seeded admin user to the admin role, if present.
        User::where('email', 'admin@elsol.uy')->update(['role_id' => $admin->id]);
    }
}
