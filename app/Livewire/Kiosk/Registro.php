<?php

namespace App\Livewire\Kiosk;

use App\Domain\Fichaje\FichajeInvalidoException;
use App\Domain\Fichaje\RegistrarFichaje;
use App\Enums\TipoFichaje;
use App\Models\Fichaje;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Touch-first kiosk punch screen.
 *
 * The operator types an employee DNI on the on-screen keypad and presses
 * ENTRADA or SALIDA. All business rules (DNI resolution and day-bounded
 * alternation) live in the RegistrarFichaje domain service; this component
 * only drives the interaction and surfaces the domain's Spanish messages.
 */
#[Layout('components.layouts.kiosk')]
class Registro extends Component
{
    public string $dni = '';

    public string $successMessage = '';

    public string $errorMessage = '';

    /** Id of the fichaje whose observation is being edited, if any. */
    public ?int $editandoId = null;

    public string $observacionEdit = '';

    /** Append a digit typed on the numeric keypad to the DNI display. */
    public function appendDigit(string $digit): void
    {
        if (ctype_digit($digit) && strlen($this->dni) < 12) {
            $this->dni .= $digit;
        }
        $this->resetMessages();
    }

    /** Clear the DNI display (Borrar). */
    public function clear(): void
    {
        $this->dni = '';
        $this->resetMessages();
    }

    /**
     * Register an entrada or salida for the DNI on screen.
     *
     * The only two write actions the kiosk (and the fichaje role) may perform
     * are creating a punch and, below, adding an observation to one.
     */
    public function registrar(string $tipo, RegistrarFichaje $service): void
    {
        $this->resetMessages();

        $dni = trim($this->dni);

        if ($dni === '') {
            $this->errorMessage = 'Ingrese un DNI antes de registrar.';

            return;
        }

        try {
            $service->registrarPorDni($dni, TipoFichaje::from($tipo));

            $this->dni = '';
            $this->successMessage = 'Registro ingresado correctamente';
        } catch (FichajeInvalidoException $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    /** Open the inline observation editor for a given fichaje. */
    public function editarObservacion(int $fichajeId): void
    {
        $fichaje = Fichaje::find($fichajeId);

        if ($fichaje === null) {
            return;
        }

        $this->editandoId = $fichaje->id;
        $this->observacionEdit = (string) $fichaje->observacion;
        $this->resetMessages();
    }

    /** Persist the edited observation for the open fichaje. */
    public function guardarObservacion(): void
    {
        if ($this->editandoId === null) {
            return;
        }

        $fichaje = Fichaje::find($this->editandoId);

        if ($fichaje !== null) {
            $observacion = trim($this->observacionEdit);
            $fichaje->observacion = $observacion === '' ? null : $observacion;
            $fichaje->save();

            $this->successMessage = 'Observación guardada.';
        }

        $this->cancelarObservacion();
    }

    /** Close the inline observation editor without saving. */
    public function cancelarObservacion(): void
    {
        $this->editandoId = null;
        $this->observacionEdit = '';
    }

    private function resetMessages(): void
    {
        $this->successMessage = '';
        $this->errorMessage = '';
    }

    public function render()
    {
        $fichajes = Fichaje::with('personal')
            ->orderByDesc('fecha_hora')
            ->orderByDesc('id')
            ->limit(50)
            ->get();

        return view('livewire.kiosk.registro', [
            'fichajes' => $fichajes,
            'operador' => Auth::user(),
        ]);
    }
}
