<?php

namespace App\Http\Controllers;

use App\Domain\Fichaje\Quincenas;
use App\Domain\Fichaje\ReporteHoras;
use App\Models\Personal;
use App\Support\Duracion;
use App\Support\Meses;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Admin-guarded PDF exports for the worked-hours reports.
 *
 * Routes are protected by the "admin.access" middleware, mirroring the
 * admin panel gate.
 */
class HorasPdfController extends Controller
{
    public function __construct(
        private readonly ReporteHoras $reporte,
        private readonly Quincenas $quincenas,
    ) {}

    /**
     * Monthly summary across every employee with movements.
     */
    public function resumen(Request $request): Response
    {
        $mes = (int) $request->integer('mes', (int) now()->month);
        $anio = (int) $request->integer('anio', (int) now()->year);

        $filas = $this->reporte->empleadosConMovimientos($mes, $anio)
            ->map(function (Personal $persona) use ($mes, $anio): array {
                $resumen = $this->reporte->resumenMensual($persona, $mes, $anio);

                return [
                    'persona' => "{$persona->apellido}, {$persona->nombre}",
                    'mensual' => Duracion::hms($resumen['mensual']),
                    'primera' => Duracion::hms($resumen['primeraQuincena']),
                    'segunda' => Duracion::hms($resumen['segundaQuincena']),
                ];
            })
            ->all();

        $pdf = Pdf::loadView('pdf.resumen-horas', [
            'mesNombre' => Meses::nombre($mes),
            'anio' => $anio,
            'filas' => $filas,
        ]);

        return $pdf->stream("resumen-horas-{$anio}-{$mes}.pdf");
    }

    /**
     * Per-person detail for a period.
     */
    public function detalle(Request $request): Response
    {
        $mes = (int) $request->integer('mes', (int) now()->month);
        $anio = (int) $request->integer('anio', (int) now()->year);
        $persona = Personal::findOrFail((int) $request->integer('personal'));

        $resumen = $this->reporte->resumenMensual($persona, $mes, $anio);
        $quincenas = $this->quincenas->etiquetas($mes, $anio);

        $dias = collect($this->reporte->detallePorDia($persona, $mes, $anio))
            ->map(fn (array $fila): array => [
                'fecha' => $fila['fecha']->format('d-m-Y'),
                'horas' => Duracion::hms($fila['segundos']),
                'observaciones' => $fila['observaciones'],
            ])
            ->all();

        $pares = collect($this->reporte->pares($persona, $mes, $anio))
            ->map(fn (array $par): array => [
                'fecha' => $par['fecha']->format('d-m-Y'),
                'ingreso' => $par['ingreso']->format('H:i:s'),
                'egreso' => $par['egreso']?->format('H:i:s') ?? '—',
                'cerrado' => $par['cerrado'],
            ])
            ->all();

        $pdf = Pdf::loadView('pdf.detalle-horas', [
            'personaNombre' => "{$persona->apellido}, {$persona->nombre}",
            'periodo' => Meses::periodo($mes, $anio),
            'rangoPrimera' => $quincenas['primera'],
            'rangoSegunda' => $quincenas['segunda'],
            'totalMensual' => Duracion::hms($resumen['mensual']),
            'totalPrimera' => Duracion::hms($resumen['primeraQuincena']),
            'totalSegunda' => Duracion::hms($resumen['segundaQuincena']),
            'dias' => $dias,
            'pares' => $pares,
        ]);

        return $pdf->stream("detalle-horas-{$persona->id}-{$anio}-{$mes}.pdf");
    }
}
