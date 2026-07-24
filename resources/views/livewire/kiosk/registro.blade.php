<div class="kiosk-shell">
    <header class="kiosk-header">
        <span class="kiosk-sun">@include('partials.sol')</span>
        <span class="brand-name">El Solar Uruguay</span>
        <span class="spacer"></span>
        <span class="who">
            Operador: <strong>{{ $operador?->name }}</strong><br>
            <a href="/logout" onclick="event.preventDefault(); document.getElementById('kiosk-logout').submit();">Salir</a>
            <form id="kiosk-logout" action="/logout" method="POST" style="display:none">@csrf</form>
        </span>
    </header>

    <main class="kiosk-main">
        @if ($successMessage)
            <div class="flash ok" wire:key="flash-ok">{{ $successMessage }}</div>
        @endif
        @if ($errorMessage)
            <div class="flash err" wire:key="flash-err">{{ $errorMessage }}</div>
        @endif

        <div class="grid-top">
            <section class="panel">
                <p class="dni-label">DNI del empleado</p>
                <div class="dni-display" aria-live="polite">{{ $dni !== '' ? $dni : '—' }}</div>

                <div class="keypad">
                    @foreach (['1','2','3','4','5','6','7','8','9'] as $n)
                        <button type="button" class="key" wire:click="appendDigit('{{ $n }}')">{{ $n }}</button>
                    @endforeach
                    <button type="button" class="key" wire:click="appendDigit('0')">0</button>
                    <button type="button" class="key clear wide" wire:click="clear">Borrar</button>
                </div>

                <div class="actions">
                    <button type="button" class="btn-big btn-entrada" wire:click="registrar('entrada')">ENTRADA</button>
                    <button type="button" class="btn-big btn-salida" wire:click="registrar('salida')">SALIDA</button>
                </div>
            </section>

            <section class="panel">
                <p class="list-title">Últimos registros</p>
                <div class="table-wrap">
                    <table class="fichajes">
                        <thead>
                            <tr>
                                <th>Persona</th>
                                <th>Tipo</th>
                                <th>Fecha/Hora</th>
                                <th>Observación</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($fichajes as $fichaje)
                                <tr wire:key="fichaje-{{ $fichaje->id }}">
                                    <td>{{ $fichaje->personal?->apellido }}, {{ $fichaje->personal?->nombre }}</td>
                                    <td>
                                        <span class="badge {{ $fichaje->tipo->value }}">
                                            {{ strtoupper($fichaje->tipo->value) }}
                                        </span>
                                    </td>
                                    <td>{{ $fichaje->fecha_hora?->format('d-m-Y H:i:s') }}</td>
                                    <td>
                                        @if ($editandoId === $fichaje->id)
                                            <div class="obs-editor">
                                                <textarea wire:model="observacionEdit" placeholder="Observación..."></textarea>
                                                <div class="row">
                                                    <button type="button" class="btn-save" wire:click="guardarObservacion">Guardar</button>
                                                    <button type="button" class="btn-cancel" wire:click="cancelarObservacion">Cancelar</button>
                                                </div>
                                            </div>
                                        @else
                                            @if ($fichaje->observacion)
                                                <div class="obs-text">{{ $fichaje->observacion }}</div>
                                            @endif
                                            <button type="button" class="btn-obs" wire:click="editarObservacion({{ $fichaje->id }})">
                                                {{ $fichaje->observacion ? 'Editar' : 'Agregar' }} observación
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" style="text-align:center;color:var(--muted);padding:24px">Sin registros todavía.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </main>
</div>
