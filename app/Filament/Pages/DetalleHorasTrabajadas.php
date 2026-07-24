<?php

namespace App\Filament\Pages;

use App\Domain\Fichaje\ReporteHoras;
use App\Models\Personal;
use App\Support\Duracion;
use App\Support\Meses;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

/**
 * Screen B — worked-hours detail for one person in one period.
 *
 * Reached from Screen A ("Obtener Información" / "Ver Detalle"). Not part of
 * the navigation; access is still enforced by the panel gate (admins only).
 */
class DetalleHorasTrabajadas extends Page
{
    protected static ?string $navigationGroup = 'Horarios';

    protected static bool $shouldRegisterNavigation = false;

    protected static string $view = 'filament.pages.detalle-horas-trabajadas';

    public int $mes;

    public int $anio;

    public int $personal;

    public function mount(): void
    {
        $this->personal = (int) request()->integer('personal');
        $this->mes = (int) request()->integer('mes', (int) now()->month);
        $this->anio = (int) request()->integer('anio', (int) now()->year);
    }

    public function getTitle(): string|Htmlable
    {
        return 'Detalle de Horas Trabajadas';
    }

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        $persona = Personal::findOrFail($this->personal);
        $reporte = app(ReporteHoras::class);

        $resumen = $reporte->resumenMensual($persona, $this->mes, $this->anio);

        $dias = collect($reporte->detallePorDia($persona, $this->mes, $this->anio))
            ->map(fn (array $fila): array => [
                'fecha' => $fila['fecha']->format('d-m-Y'),
                'horas' => Duracion::hms($fila['segundos']),
                'observaciones' => $fila['observaciones'],
            ])
            ->all();

        $pares = collect($reporte->pares($persona, $this->mes, $this->anio))
            ->map(fn (array $par): array => [
                'fecha' => $par['fecha']->format('d-m-Y'),
                'ingreso' => $par['ingreso']->format('H:i:s'),
                'egreso' => $par['egreso']?->format('H:i:s') ?? '—',
                'cerrado' => $par['cerrado'],
            ])
            ->all();

        return [
            'personaNombre' => "{$persona->apellido}, {$persona->nombre}",
            'periodo' => Meses::periodo($this->mes, $this->anio),
            'totalMensual' => Duracion::hms($resumen['mensual']),
            'totalPrimera' => Duracion::hms($resumen['primeraQuincena']),
            'totalSegunda' => Duracion::hms($resumen['segundaQuincena']),
            'dias' => $dias,
            'pares' => $pares,
            'volverUrl' => GestionHorasTrabajadas::getUrl(),
            'detallePdfUrl' => route('admin.horas.detalle', [
                'personal' => $this->personal,
                'mes' => $this->mes,
                'anio' => $this->anio,
            ]),
        ];
    }
}
