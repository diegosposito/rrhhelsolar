<?php

use App\Filament\Pages\DetalleHorasTrabajadas;
use App\Filament\Pages\GestionHorasTrabajadas;
use App\Models\Personal;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Create a user bound to a role by name, with the given active state.
 */
function makeReportUser(string $roleName, bool $activo): User
{
    $role = Role::factory()->create(['nombre' => $roleName]);

    return User::factory()->create([
        'role_id' => $role->id,
        'activo' => $activo,
    ]);
}

it('grants an active admin access to the report page', function () {
    $admin = makeReportUser('admin', true);

    $this->actingAs($admin)
        ->get(GestionHorasTrabajadas::getUrl())
        ->assertSuccessful();
});

it('denies a fichaje user access to the report page', function () {
    $fichaje = makeReportUser('fichaje', true);

    $this->actingAs($fichaje)
        ->get(GestionHorasTrabajadas::getUrl())
        ->assertForbidden();
});

it('redirects a guest away from the report page to the panel login', function () {
    $this->get(GestionHorasTrabajadas::getUrl())
        ->assertRedirect('/admin/login');
});

it('lets an active admin download the summary PDF', function () {
    $admin = makeReportUser('admin', true);

    $this->actingAs($admin)
        ->get(route('admin.horas.resumen', ['mes' => 7, 'anio' => 2026]))
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');
});

it('lets an active admin download the detail PDF', function () {
    $admin = makeReportUser('admin', true);
    $persona = Personal::factory()->create();

    $this->actingAs($admin)
        ->get(route('admin.horas.detalle', ['personal' => $persona->id, 'mes' => 7, 'anio' => 2026]))
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');
});

it('denies a fichaje user the summary PDF', function () {
    $fichaje = makeReportUser('fichaje', true);

    $this->actingAs($fichaje)
        ->get(route('admin.horas.resumen', ['mes' => 7, 'anio' => 2026]))
        ->assertForbidden();
});

it('denies a fichaje user the detail PDF', function () {
    $fichaje = makeReportUser('fichaje', true);
    $persona = Personal::factory()->create();

    $this->actingAs($fichaje)
        ->get(route('admin.horas.detalle', ['personal' => $persona->id, 'mes' => 7, 'anio' => 2026]))
        ->assertForbidden();
});

/**
 * The Filament button component escapes the href itself. Interpolating the URL
 * with "{{ }}" escaped it a second time, so the browser sent "&amp;mes=5" and
 * PHP parsed the period as "amp;mes" — the export silently fell back to the
 * current month and printed a different (often empty) period.
 */
it('renders the summary PDF link with single-escaped query separators', function () {
    $admin = makeReportUser('admin', true);

    $html = $this->actingAs($admin)
        ->get(GestionHorasTrabajadas::getUrl())
        ->assertSuccessful()
        ->getContent();

    expect($html)->not->toContain('&amp;amp;')
        ->and($html)->toContain('resumen.pdf?mes='.now()->month.'&amp;anio='.now()->year);
});

it('renders the detail PDF link with single-escaped query separators', function () {
    $admin = makeReportUser('admin', true);
    $persona = Personal::factory()->create();

    $html = $this->actingAs($admin)
        ->get(DetalleHorasTrabajadas::getUrl([
            'personal' => $persona->id,
            'mes' => 5,
            'anio' => 2026,
        ]))
        ->assertSuccessful()
        ->getContent();

    expect($html)->not->toContain('&amp;amp;')
        ->and($html)->toContain("detalle.pdf?personal={$persona->id}&amp;mes=5&amp;anio=2026");
});

it('redirects a guest away from the PDF routes', function () {
    // The framework "auth" middleware sends unauthenticated users to the
    // app login route before the admin gate is reached.
    $this->get(route('admin.horas.resumen', ['mes' => 7, 'anio' => 2026]))
        ->assertRedirect('/login');
});
