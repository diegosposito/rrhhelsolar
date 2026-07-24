<x-filament-panels::page>
    <x-filament::section>
        <form wire:submit="obtenerInformacion">
            {{ $this->form }}

            <div class="mt-6 flex flex-wrap items-center gap-3">
                <x-filament::button type="submit" icon="heroicon-m-magnifying-glass">
                    Obtener Información
                </x-filament::button>

                <x-filament::button
                    tag="a"
                    href="{{ $this->resumenPdfUrl }}"
                    color="gray"
                    icon="heroicon-m-printer"
                    target="_blank"
                >
                    Imprimir Resumen
                </x-filament::button>
            </div>
        </form>
    </x-filament::section>

    <x-filament::section>
        <x-slot name="heading">
            Resumen mensual — {{ $this->periodo }}
        </x-slot>

        <x-slot name="description">
            Empleados con movimientos en el período. Valores en HH:MM:SS.
        </x-slot>

        @php($resumen = $this->resumen)

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left border-collapse">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-white/10 text-gray-500 dark:text-gray-400">
                        <th class="py-2 pr-4 font-medium">Persona</th>
                        <th class="py-2 pr-4 font-medium">Mensual</th>
                        <th class="py-2 pr-4 font-medium">1er Quinc</th>
                        <th class="py-2 pr-4 font-medium">2da Quinc</th>
                        <th class="py-2 pr-4 font-medium text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($resumen as $fila)
                        <tr class="border-b border-gray-100 dark:border-white/5">
                            <td class="py-2 pr-4 font-medium text-gray-950 dark:text-white">{{ $fila['persona'] }}</td>
                            <td class="py-2 pr-4 tabular-nums">{{ $fila['mensual'] }}</td>
                            <td class="py-2 pr-4 tabular-nums">{{ $fila['primera'] }}</td>
                            <td class="py-2 pr-4 tabular-nums">{{ $fila['segunda'] }}</td>
                            <td class="py-2 pr-4 text-right">
                                <x-filament::button
                                    size="sm"
                                    color="primary"
                                    icon="heroicon-m-eye"
                                    wire:click="verDetalle({{ $fila['id'] }})"
                                >
                                    Ver Detalle
                                </x-filament::button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-6 text-center text-gray-500 dark:text-gray-400">
                                No hay empleados con movimientos en este período.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-filament::section>
</x-filament-panels::page>
