<?php

namespace Database\Seeders;

use App\Enums\TipoFichaje;
use App\Models\Fichaje;
use App\Models\Personal;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Development/demo data for the worked-hours report.
 *
 * Seeds a handful of active personal and a realistic set of fichajes for the
 * CURRENT month: weekday entrada/salida pairs (some days with two pairs) plus
 * a trailing orphan entrada to exercise the open/red state. Runs only outside
 * production; wired into DatabaseSeeder so `migrate:fresh --seed` includes it
 * in dev. It never touches the seeded admin@elsol.uy account.
 */
class HorasDemoSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure a kiosk (fichaje) demo account exists alongside the admin.
        $fichajeRole = Role::firstOrCreate(
            ['nombre' => 'fichaje'],
            ['descripcion' => 'Time-clock kiosk access'],
        );

        User::firstOrCreate(
            ['email' => 'kiosk@elsol.uy'],
            [
                'name' => 'Kiosco Fichaje',
                'password' => Hash::make('password'),
                'activo' => true,
                'role_id' => $fichajeRole->id,
            ],
        );

        $empleados = [
            ['apellido' => 'Alvarez', 'nombre' => 'Beatriz', 'dni' => '30111222'],
            ['apellido' => 'Benitez', 'nombre' => 'Carlos', 'dni' => '28333444'],
            ['apellido' => 'Fernandez', 'nombre' => 'Lucia', 'dni' => '35555666'],
            ['apellido' => 'Gonzalez', 'nombre' => 'Martin', 'dni' => '27777888'],
            ['apellido' => 'Rodriguez', 'nombre' => 'Sofia', 'dni' => '31999000'],
            ['apellido' => 'Suarez', 'nombre' => 'Diego', 'dni' => '29222333'],
            ['apellido' => 'Zapata', 'nombre' => 'Valentina', 'dni' => '33444555'],
        ];

        $hoy = Carbon::today();
        $inicioMes = $hoy->copy()->startOfMonth();

        foreach ($empleados as $indice => $datos) {
            $personal = Personal::updateOrCreate(
                ['dni' => $datos['dni']],
                [
                    'nombre' => $datos['nombre'],
                    'apellido' => $datos['apellido'],
                    'area' => ['Producción', 'Administración', 'Depósito'][$indice % 3],
                    'activo' => true,
                ],
            );

            $this->seedFichajesDelMes($personal, $inicioMes, $hoy, $indice);
        }
    }

    /**
     * Seed weekday punches for the current month up to (and including) today.
     */
    private function seedFichajesDelMes(Personal $personal, Carbon $inicioMes, Carbon $hoy, int $indice): void
    {
        // Avoid duplicate punches if the seeder is re-run.
        $personal->fichajes()
            ->whereBetween('fecha_hora', [$inicioMes->copy()->startOfMonth(), $inicioMes->copy()->endOfMonth()])
            ->delete();

        $diasHabiles = collect(CarbonPeriod::create($inicioMes, $hoy))
            ->filter(fn (Carbon $dia): bool => ! $dia->isWeekend())
            ->values();

        $ultimoDia = $diasHabiles->last();

        foreach ($diasHabiles as $posicion => $dia) {
            // Trailing orphan: the first employee forgets to punch out on the
            // last seeded weekday (shows the ✗/red open state in the report).
            // Evaluated before the absence rule so the orphan is guaranteed.
            if ($indice === 0 && $ultimoDia !== null && $dia->isSameDay($ultimoDia)) {
                $this->punch($personal, TipoFichaje::Entrada, $dia, '08:05:00', 'Olvido marcar salida');

                continue;
            }

            // Occasionally an employee is absent (skip the day).
            if (($indice + $posicion) % 7 === 3) {
                continue;
            }

            $tarde = ($posicion % 3 === 0);

            // Morning pair.
            $this->punch($personal, TipoFichaje::Entrada, $dia, $tarde ? '08:12:30' : '08:00:00', $tarde ? 'Llega tarde' : null);
            $this->punch($personal, TipoFichaje::Salida, $dia, '12:00:00');

            // Afternoon pair (skipped on some days to vary totals).
            if (($indice + $posicion) % 4 !== 1) {
                $this->punch($personal, TipoFichaje::Entrada, $dia, '14:00:00');
                $this->punch($personal, TipoFichaje::Salida, $dia, $posicion % 2 === 0 ? '17:00:00' : '17:30:45');
            }
        }
    }

    private function punch(Personal $personal, TipoFichaje $tipo, Carbon $dia, string $hora, ?string $observacion = null): void
    {
        Fichaje::create([
            'personal_id' => $personal->id,
            'tipo' => $tipo,
            'fecha_hora' => $dia->copy()->setTimeFromTimeString($hora),
            'observacion' => $observacion,
        ]);
    }
}
