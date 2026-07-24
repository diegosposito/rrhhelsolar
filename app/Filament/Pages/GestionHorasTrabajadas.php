<?php

namespace App\Filament\Pages;

use App\Domain\Fichaje\ReporteHoras;
use App\Models\Personal;
use App\Support\Duracion;
use App\Support\Meses;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

/**
 * Screen A — monthly worked-hours management.
 *
 * Filters (Mes / Año / Persona) drive a current-period overview of every
 * employee with movements. "Obtener Información" requires a selected person
 * and opens their detail; each overview row also links to its detail.
 *
 * Access is enforced by the panel gate (User::canAccessPanel): only active
 * admins may reach this page.
 */
class GestionHorasTrabajadas extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationGroup = 'Horarios';

    protected static ?string $navigationLabel = 'Gestión Horas Trabajadas';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.gestion-horas-trabajadas';

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    public function getTitle(): string|Htmlable
    {
        return 'Gestión Horas Trabajadas';
    }

    public function mount(): void
    {
        $this->form->fill([
            'mes' => (int) now()->month,
            'anio' => (int) now()->year,
            'personal_id' => null,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('mes')
                    ->label('Mes')
                    ->options(Meses::opciones())
                    ->required()
                    ->native(false)
                    ->live(),
                Select::make('anio')
                    ->label('Año')
                    ->options($this->opcionesAnio())
                    ->required()
                    ->native(false)
                    ->live(),
                Select::make('personal_id')
                    ->label('Persona')
                    ->options(
                        Personal::query()
                            ->orderBy('apellido')
                            ->orderBy('nombre')
                            ->get()
                            ->mapWithKeys(fn (Personal $p): array => [
                                $p->id => "{$p->apellido}, {$p->nombre}",
                            ])
                            ->all()
                    )
                    ->searchable()
                    ->native(false)
                    ->placeholder('Seleccione una persona'),
            ])
            ->columns(3)
            ->statePath('data');
    }

    /**
     * "Obtener Información" — requires a selected person, then opens the detail.
     */
    public function obtenerInformacion(): void
    {
        $estado = $this->form->getState();

        if (empty($estado['personal_id'])) {
            Notification::make()
                ->danger()
                ->title('Seleccione una persona')
                ->body('Debe seleccionar una persona para obtener el detalle.')
                ->send();

            return;
        }

        $this->redirect(DetalleHorasTrabajadas::getUrl([
            'personal' => $estado['personal_id'],
            'mes' => $estado['mes'],
            'anio' => $estado['anio'],
        ]));
    }

    /**
     * "Ver Detalle" from an overview row.
     */
    public function verDetalle(int $personalId): void
    {
        $this->redirect(DetalleHorasTrabajadas::getUrl([
            'personal' => $personalId,
            'mes' => $this->data['mes'],
            'anio' => $this->data['anio'],
        ]));
    }

    /**
     * Overview rows for every employee with movements in the selected period.
     *
     * @return list<array{id: int, persona: string, mensual: string, primera: string, segunda: string}>
     */
    public function getResumenProperty(): array
    {
        $mes = (int) ($this->data['mes'] ?? now()->month);
        $anio = (int) ($this->data['anio'] ?? now()->year);

        $reporte = app(ReporteHoras::class);

        return $reporte->empleadosConMovimientos($mes, $anio)
            ->map(function (Personal $persona) use ($reporte, $mes, $anio): array {
                $resumen = $reporte->resumenMensual($persona, $mes, $anio);

                return [
                    'id' => $persona->id,
                    'persona' => "{$persona->apellido}, {$persona->nombre}",
                    'mensual' => Duracion::hms($resumen['mensual']),
                    'primera' => Duracion::hms($resumen['primeraQuincena']),
                    'segunda' => Duracion::hms($resumen['segundaQuincena']),
                ];
            })
            ->all();
    }

    public function getPeriodoProperty(): string
    {
        return Meses::periodo(
            (int) ($this->data['mes'] ?? now()->month),
            (int) ($this->data['anio'] ?? now()->year),
        );
    }

    public function getResumenPdfUrlProperty(): string
    {
        return route('admin.horas.resumen', [
            'mes' => $this->data['mes'] ?? now()->month,
            'anio' => $this->data['anio'] ?? now()->year,
        ]);
    }

    /**
     * @return array<int, int>
     */
    private function opcionesAnio(): array
    {
        $actual = (int) now()->year;

        return collect(range($actual - 5, $actual + 1))
            ->mapWithKeys(fn (int $anio): array => [$anio => $anio])
            ->all();
    }
}
