<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 24px 28px; }
        * { font-family: DejaVu Sans, sans-serif; }
        body { color: #1f2937; font-size: 11px; }
        .brand {
            border-bottom: 3px solid #a10a6b;
            padding-bottom: 10px;
            margin-bottom: 14px;
        }
        .brand-name { color: #a10a6b; font-size: 18px; font-weight: bold; }
        .brand-sun {
            display: inline-block;
            width: 16px; height: 16px;
            border-radius: 50%;
            background: #a10a6b;
            vertical-align: middle;
            margin-right: 6px;
        }
        h1 { font-size: 14px; color: #111827; margin: 4px 0 0; }
        .meta { margin: 2px 0; color: #374151; }
        .totales { margin: 12px 0 4px; }
        .totales td {
            padding: 8px 10px; border: 1px solid #e5e7eb; width: 33%;
        }
        .totales .lbl { font-size: 9px; text-transform: uppercase; color: #6b7280; }
        .totales .val { font-size: 15px; font-weight: bold; color: #a10a6b; }
        h2 { font-size: 12px; color: #a10a6b; margin: 18px 0 4px; }
        table.data { width: 100%; border-collapse: collapse; margin-top: 6px; }
        table.data th {
            background: #a10a6b; color: #ffffff; text-align: left;
            padding: 5px 8px; font-size: 10px;
        }
        table.data td { padding: 5px 8px; border-bottom: 1px solid #e5e7eb; }
        table.data tr:nth-child(even) td { background: #faf5f9; }
        .num { text-align: right; font-variant-numeric: tabular-nums; }
        .center { text-align: center; }
        .ok { color: #059669; font-weight: bold; }
        .bad { color: #dc2626; font-weight: bold; }
        .empty { text-align: center; color: #6b7280; padding: 12px; }
    </style>
</head>
<body>
    <div class="brand">
        <span class="brand-sun"></span>
        <span class="brand-name">El Solar Uruguay</span>
        <h1>Detalle de Horas Trabajadas</h1>
        <div class="meta"><strong>Persona:</strong> {{ $personaNombre }}</div>
        <div class="meta"><strong>Período informado:</strong> {{ $periodo }}</div>
    </div>

    <table class="totales">
        <tr>
            <td>
                <div class="lbl">Total horas del mes</div>
                <div class="val">{{ $totalMensual }}</div>
            </td>
            <td>
                <div class="lbl">Total Primer Quincena</div>
                <div class="val">{{ $totalPrimera }}</div>
            </td>
            <td>
                <div class="lbl">Total Segunda Quincena</div>
                <div class="val">{{ $totalSegunda }}</div>
            </td>
        </tr>
    </table>

    <h2>Detalle por día</h2>
    <table class="data">
        <thead>
            <tr>
                <th>Persona</th>
                <th>Fecha</th>
                <th>Horas Trabajadas</th>
                <th>Observaciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($dias as $fila)
                <tr>
                    <td>{{ $personaNombre }}</td>
                    <td class="num">{{ $fila['fecha'] }}</td>
                    <td class="num">{{ $fila['horas'] }}</td>
                    <td>{{ $fila['observaciones'] ?: '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="empty">Sin movimientos en el período.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Detalle por par (ingreso / egreso)</h2>
    <table class="data">
        <thead>
            <tr>
                <th>Persona</th>
                <th>Fecha</th>
                <th>Hora Ingreso</th>
                <th>Hora Egreso</th>
                <th class="center">Estado</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($pares as $par)
                <tr>
                    <td>{{ $personaNombre }}</td>
                    <td class="num">{{ $par['fecha'] }}</td>
                    <td class="num">{{ $par['ingreso'] }}</td>
                    <td class="num">{{ $par['egreso'] }}</td>
                    <td class="center">
                        @if ($par['cerrado'])
                            <span class="ok">&#10003;</span>
                        @else
                            <span class="bad">&#10007;</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="empty">Sin movimientos en el período.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
