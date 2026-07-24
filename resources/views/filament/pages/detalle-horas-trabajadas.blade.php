<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">{{ $personaNombre }}</x-slot>
        <x-slot name="description">Período informado: {{ $periodo }}</x-slot>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="rounded-xl bg-primary-50 p-4 dark:bg-primary-500/10">
                <div class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Total horas del mes</div>
                <div class="mt-1 text-2xl font-bold tabular-nums text-primary-600 dark:text-primary-400">{{ $totalMensual }}</div>
            </div>
            <div class="rounded-xl bg-gray-50 p-4 dark:bg-white/5">
                <div class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Total Primer Quincena</div>
                <div class="mt-1 text-2xl font-bold tabular-nums text-gray-950 dark:text-white">{{ $totalPrimera }}</div>
            </div>
            <div class="rounded-xl bg-gray-50 p-4 dark:bg-white/5">
                <div class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Total Segunda Quincena</div>
                <div class="mt-1 text-2xl font-bold tabular-nums text-gray-950 dark:text-white">{{ $totalSegunda }}</div>
            </div>
        </div>

        <div class="mt-6 flex flex-wrap items-center gap-3">
            <x-filament::button tag="a" href="{{ $volverUrl }}" color="gray" icon="heroicon-m-arrow-left">
                Volver al listado
            </x-filament::button>

            <x-filament::button tag="a" href="{{ $detallePdfUrl }}" color="primary" icon="heroicon-m-printer" target="_blank">
                Imprimir Detalle
            </x-filament::button>
        </div>
    </x-filament::section>

    <x-filament::section>
        <x-slot name="heading">Detalle por día</x-slot>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left border-collapse">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-white/10 text-gray-500 dark:text-gray-400">
                        <th class="py-2 pr-4 font-medium">Persona</th>
                        <th class="py-2 pr-4 font-medium">Fecha</th>
                        <th class="py-2 pr-4 font-medium">Horas Trabajadas</th>
                        <th class="py-2 pr-4 font-medium">Observaciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($dias as $fila)
                        <tr class="border-b border-gray-100 dark:border-white/5">
                            <td class="py-2 pr-4">{{ $personaNombre }}</td>
                            <td class="py-2 pr-4 tabular-nums">{{ $fila['fecha'] }}</td>
                            <td class="py-2 pr-4 tabular-nums">{{ $fila['horas'] }}</td>
                            <td class="py-2 pr-4 text-gray-500 dark:text-gray-400">{{ $fila['observaciones'] ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-6 text-center text-gray-500 dark:text-gray-400">Sin movimientos en el período.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-filament::section>

    <x-filament::section>
        <x-slot name="heading">Detalle por par (ingreso / egreso)</x-slot>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left border-collapse">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-white/10 text-gray-500 dark:text-gray-400">
                        <th class="py-2 pr-4 font-medium">Persona</th>
                        <th class="py-2 pr-4 font-medium">Fecha</th>
                        <th class="py-2 pr-4 font-medium">Hora Ingreso</th>
                        <th class="py-2 pr-4 font-medium">Hora Egreso</th>
                        <th class="py-2 pr-4 font-medium text-center">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pares as $par)
                        <tr class="border-b border-gray-100 dark:border-white/5">
                            <td class="py-2 pr-4">{{ $personaNombre }}</td>
                            <td class="py-2 pr-4 tabular-nums">{{ $par['fecha'] }}</td>
                            <td class="py-2 pr-4 tabular-nums">{{ $par['ingreso'] }}</td>
                            <td class="py-2 pr-4 tabular-nums">{{ $par['egreso'] }}</td>
                            <td class="py-2 pr-4 text-center">
                                @if ($par['cerrado'])
                                    <x-filament::icon icon="heroicon-m-check-circle" class="inline-block h-5 w-5 text-success-500" />
                                @else
                                    <x-filament::icon icon="heroicon-m-x-circle" class="inline-block h-5 w-5 text-danger-500" />
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-6 text-center text-gray-500 dark:text-gray-400">Sin movimientos en el período.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-filament::section>
</x-filament-panels::page>
