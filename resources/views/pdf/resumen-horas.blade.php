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
            margin-bottom: 16px;
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
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th {
            background: #a10a6b; color: #ffffff; text-align: left;
            padding: 6px 8px; font-size: 11px;
        }
        td { padding: 6px 8px; border-bottom: 1px solid #e5e7eb; }
        tr:nth-child(even) td { background: #faf5f9; }
        .num { text-align: right; font-variant-numeric: tabular-nums; }
        .empty { text-align: center; color: #6b7280; padding: 16px; }
    </style>
</head>
<body>
    <div class="brand">
        <span class="brand-sun"></span>
        <span class="brand-name">El Solar Uruguay</span>
        <h1>Resumen Mensual Horas Trabajadas: {{ $mesNombre }} del {{ $anio }}</h1>
    </div>

    <table>
        <thead>
            <tr>
                <th>Persona</th>
                <th>Mensual</th>
                <th>1er Quinc</th>
                <th>2da Quinc</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($filas as $fila)
                <tr>
                    <td>{{ $fila['persona'] }}</td>
                    <td class="num">{{ $fila['mensual'] }}</td>
                    <td class="num">{{ $fila['primera'] }}</td>
                    <td class="num">{{ $fila['segunda'] }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="empty">No hay empleados con movimientos en este período.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
