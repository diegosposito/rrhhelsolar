<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Ensure the admin user exists before roles are assigned.
        User::firstOrCreate(
            ['email' => 'admin@elsol.uy'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('password'),
                'activo' => true,
            ],
        );

        $this->call(RolesSeeder::class);

        // Development-only demo data for the worked-hours report. Skipped in
        // production so it never pollutes real data.
        if (! app()->isProduction()) {
            $this->call(HorasDemoSeeder::class);
        }
    }
}
