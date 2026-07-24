<?php

namespace App\Console\Commands;

use App\Enums\TipoFichaje;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migrates the six in-scope entities from the legacy Symfony 1.4 database
 * (imported as a subset into the `legacy` connection) into the new schema.
 *
 * Source -> target mapping:
 *   obras_sociales -> obras_sociales
 *   personas       -> personal   (DNI-deduplicated: survivor = most fichajes)
 *   paciente       -> pacientes  (active only)
 *   horarios       -> fichajes   (tiporegistro 1=entrada / 0=salida, anulado=0)
 *
 * Users and roles are NOT migrated (recreated by seeders). Safe to re-run:
 * it truncates the four data tables before importing.
 */
class MigrarLegacy extends Command
{
    protected $signature = 'app:migrar-legacy {--connection=legacy : Legacy DB connection name}';

    protected $description = 'Migra las 6 entidades desde la base legacy hacia el esquema nuevo';

    private const SEXO = [1 => 'Masculino', 2 => 'Femenino'];

    public function handle(): int
    {
        $legacyName = (string) $this->option('connection');
        $legacy = DB::connection($legacyName);

        try {
            $legacy->getPdo();
        } catch (\Throwable $e) {
            $this->error("No puedo conectar a la base legacy ('{$legacyName}'): {$e->getMessage()}");
            $this->line('Importá primero el subset con: mysql ... rrhh_legacy < referencia/legacy_subset.sql');

            return self::FAILURE;
        }

        $this->info('Limpiando tablas destino…');
        $this->truncateTargets();

        $obraMap = $this->migrarObrasSociales($legacy);
        [$personaMap, $personalCount] = $this->migrarPersonal($legacy);
        $pacientesCount = $this->migrarPacientes($legacy, $obraMap);
        $fichajesCount = $this->migrarFichajes($legacy, $personaMap);

        $this->newLine();
        $this->info('Migración completada.');
        $this->table(
            ['Entidad', 'Filas'],
            [
                ['Obras sociales', count($obraMap)],
                ['Personal', $personalCount],
                ['Pacientes', $pacientesCount],
                ['Fichajes', $fichajesCount],
            ]
        );

        return self::SUCCESS;
    }

    private function truncateTargets(): void
    {
        Schema::disableForeignKeyConstraints();
        foreach (['fichajes', 'pacientes', 'personal', 'obras_sociales'] as $t) {
            DB::table($t)->truncate();
        }
        Schema::enableForeignKeyConstraints();
    }

    /** @return array<int,int> legacy idobrasocial -> new obra_social id */
    private function migrarObrasSociales($legacy): array
    {
        $now = now();
        $map = [];
        foreach ($legacy->table('obras_sociales')->orderBy('idobrasocial')->get() as $row) {
            $denominacion = trim((string) $row->denominacion);
            if ($denominacion === '') {
                continue;
            }
            $id = DB::table('obras_sociales')->insertGetId([
                'denominacion' => $denominacion,
                'abreviada' => trim((string) $row->abreviada) ?: null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $map[(int) $row->idobrasocial] = $id;
        }
        $this->line('  · Obras sociales: '.count($map));

        return $map;
    }

    /**
     * Imports personas with DNI deduplication.
     *
     * @return array{0: array<int,int>, 1: int} [legacy idpersona -> new personal_id, inserted count]
     */
    private function migrarPersonal($legacy): array
    {
        // Punch counts per legacy persona (to pick the survivor on DNI collisions).
        $punchCounts = $legacy->table('horarios')
            ->where('anulado', 0)
            ->selectRaw('idpersona, COUNT(*) as c')
            ->groupBy('idpersona')
            ->pluck('c', 'idpersona')
            ->map(fn ($c) => (int) $c)
            ->toArray();

        $personas = $legacy->table('personas')->orderBy('idpersona')->get();

        // Group personas by normalized DNI.
        $byDni = [];   // dni => [rows]
        $skippedBlank = 0;
        foreach ($personas as $row) {
            $dni = $this->normalizeDni((string) $row->nrodoc);
            if ($dni === '') {
                $skippedBlank++;
                continue;
            }
            $byDni[$dni][] = $row;
        }

        $now = now();
        $personaMap = [];  // legacy idpersona -> new personal_id
        $inserted = 0;

        foreach ($byDni as $dni => $rows) {
            // Survivor = the persona with the most non-anulado fichajes (tie -> lowest idpersona).
            usort($rows, function ($a, $b) use ($punchCounts) {
                $ca = $punchCounts[(int) $a->idpersona] ?? 0;
                $cb = $punchCounts[(int) $b->idpersona] ?? 0;

                return $cb <=> $ca ?: ((int) $a->idpersona <=> (int) $b->idpersona);
            });
            $survivor = $rows[0];

            $newId = DB::table('personal')->insertGetId([
                'nombre' => trim((string) $survivor->nombre),
                'apellido' => trim((string) $survivor->apellido),
                'dni' => $dni,
                'sexo' => self::SEXO[(int) $survivor->idsexo] ?? null,
                'fecha_nacimiento' => $this->normalizeDate($survivor->fechanac),
                'fecha_ingreso' => $this->normalizeDate($survivor->fechaingreso),
                'direccion' => $this->nullable($survivor->direccion),
                'ciudad' => $this->nullable($survivor->ciudad),
                'telefono' => $this->nullable($survivor->telefono),
                'celular' => $this->nullable($survivor->celular),
                'email' => $this->nullable($survivor->email),
                'area' => null,
                'horario_semanal' => $this->nullable($survivor->horarios ?? null),
                'observaciones' => $this->nullable($survivor->otrainformacionrelevante ?? null),
                'activo' => (int) $survivor->activo === 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $inserted++;

            // Every persona in the group (survivor + merged duplicates) points to the survivor,
            // so their punches re-attach to the surviving personal record.
            foreach ($rows as $r) {
                $personaMap[(int) $r->idpersona] = $newId;
            }
        }

        $this->line("  · Personal: {$inserted} (DNI en blanco omitidos: {$skippedBlank})");

        return [$personaMap, $inserted];
    }

    /** @param array<int,int> $obraMap */
    private function migrarPacientes($legacy, array $obraMap): int
    {
        $now = now();
        $count = 0;
        foreach ($legacy->table('paciente')->orderBy('id')->get() as $row) {
            if ((int) $row->activo !== 1) {
                continue;
            }
            $nombre = trim((string) $row->nombre);
            $apellido = trim((string) $row->apellido);
            if ($nombre === '' && $apellido === '') {
                continue;
            }

            DB::table('pacientes')->insert([
                'nombre' => $nombre,
                'apellido' => $apellido,
                'direccion' => $this->nullable($row->direccion),
                'ciudad' => null,
                'celular' => $this->nullable($row->celular) ?? $this->nullable($row->telefono),
                'email' => $this->nullable($row->email),
                'observaciones' => $this->nullable($row->anotaciones ?? null),
                'obra_social_id' => $obraMap[(int) $row->idobrasocial] ?? null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $count++;
        }
        $this->line("  · Pacientes: {$count}");

        return $count;
    }

    /** @param array<int,int> $personaMap */
    private function migrarFichajes($legacy, array $personaMap): int
    {
        $entrada = TipoFichaje::Entrada->value;
        $salida = TipoFichaje::Salida->value;
        $now = now();
        $buffer = [];
        $count = 0;
        $orphans = 0;

        $legacy->table('horarios')
            ->where('anulado', 0)
            ->orderBy('id')
            ->chunk(2000, function ($rows) use (&$buffer, &$count, &$orphans, $personaMap, $entrada, $salida, $now) {
                foreach ($rows as $row) {
                    $personalId = $personaMap[(int) $row->idpersona] ?? null;
                    if ($personalId === null) {
                        $orphans++;
                        continue;
                    }
                    $buffer[] = [
                        'personal_id' => $personalId,
                        'tipo' => (int) $row->tiporegistro === 1 ? $entrada : $salida,
                        // Legacy `controlar`: only controlar=1 punches count towards
                        // worked hours (matches the trusted legacy report).
                        'contabiliza' => (int) $row->controlar === 1,
                        'fecha_hora' => $row->created_at,
                        'observacion' => $this->nullable($row->observaciones),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
                if (count($buffer) >= 2000) {
                    DB::table('fichajes')->insert($buffer);
                    $count += count($buffer);
                    $buffer = [];
                }
            });

        if ($buffer !== []) {
            DB::table('fichajes')->insert($buffer);
            $count += count($buffer);
        }

        $this->line("  · Fichajes: {$count} (huérfanos sin personal omitidos: {$orphans})");

        return $count;
    }

    private function normalizeDni(string $raw): string
    {
        // Legacy DNIs carry dots/spaces; the kiosk keypad uses the bare value.
        return preg_replace('/[\s.]+/', '', trim($raw)) ?? '';
    }

    private function normalizeDate($value): ?string
    {
        $value = trim((string) $value);
        if ($value === '' || str_starts_with($value, '0000-00-00')) {
            return null;
        }

        try {
            return Carbon::parse($value)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }

    private function nullable($value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
