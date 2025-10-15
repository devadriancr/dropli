<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Registro de Producción</title>
    <style>
        /* Fuente Arial para TODO el documento */
        * {
            font-family: Arial, sans-serif !important;
        }

        /* Página */
        @page {
            size: letter landscape;
            margin: 15mm;
        }

        /* Reset mínimo */
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html, body {
            font-size: 10px;
            color: #000;
            margin: 0;
            padding: 12px;
            font-family: Arial, sans-serif;
        }

        /* Tablas */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
            font-family: Arial, sans-serif;
        }
        th, td {
            border: 1px solid #000;
            padding: 6px 8px;
            vertical-align: middle;
            text-align: left;
            font-size: 10px;
            font-family: Arial, sans-serif;
        }

        /* Cabecera principal compacta */
        .main-header td {
            vertical-align: middle;
            font-family: Arial, sans-serif;
        }
        .logo-cell {
            width: 12%;
            padding: 4px;
            text-align: center;
            font-family: Arial, sans-serif;
        }
        .logo-cell img {
            display: block;
            max-width: 60px;
            height: auto;
            margin: 0 auto;
        }

        .title-cell {
            text-align: center;
            vertical-align: middle;
            font-weight: 700;
            font-size: 12px;
            padding: 6px;
            font-family: Arial, sans-serif;
        }

        .meta-cell {
            padding: 4px 8px;
            font-size: 10px;
            vertical-align: middle;
            text-align: left;
            font-family: Arial, sans-serif;
        }

        .code-cell {
            padding: 4px 8px;
            text-align: center;
            font-weight: 700;
            font-size: 10px;
            font-family: Arial, sans-serif;
        }

        /* Metrics */
        .metrics-table td {
            text-align: center;
            padding: 6px 8px;
            font-size: 10px;
            font-family: Arial, sans-serif;
        }
        .metrics-value {
            font-weight: 700;
            font-family: Arial, sans-serif;
        }

        /* Production */
        .production-table {
            font-size: 9px;
            font-family: Arial, sans-serif;
        }
        .production-table thead th {
            background: #f0f0f0;
            text-align: center;
            font-weight: 700;
            padding: 6px;
            font-family: Arial, sans-serif;
        }
        .production-table tbody td {
            text-align: center;
            padding: 5px;
            font-family: Arial, sans-serif;
        }
        .fixed-column {
            background: #fafafa;
            text-align: left;
            font-weight: 500;
            font-family: Arial, sans-serif;
        }
        .sub-header {
            background: #e8e8e8;
            font-weight: 700;
            text-align: center;
            font-family: Arial, sans-serif;
        }

        /* Evitar cortes internos */
        .part-group-wrapper, .part-row, tbody.part-group-body {
            page-break-inside: avoid;
            break-inside: avoid;
        }

        /* Pie / paginación */
        .page-number {
            text-align: center;
            margin-top: 8px;
            font-size: 9px;
            font-family: Arial, sans-serif;
        }
    </style>
</head>

<body>
    <!-- CABECERA: 5 columnas x 4 filas - MEJORADA PERO MANTENIENDO ESTRUCTURA -->
    <table class="main-header" role="presentation">
        <tr>
            <!-- Columna 1: Logo (rowspan 3) -->
            <td class="logo-cell" rowspan="3">
                <img src="{{ public_path('images/ykm.png') }}" alt="Logo">
            </td>

            <!-- Columnas 2-4: Título (colspan 3, rowspan 4) -->
            <td class="title-cell" colspan="3" rowspan="4">
                REGISTRO DIARIO DE PRODUCCIÓN
            </td>

            <!-- Columna 5 - fila 1: Revisión -->
            <td class="meta-cell">Revisión: <strong>1</strong></td>
        </tr>

        <tr>
            <!-- Columna 5 - fila 2: Fecha de Elaboración -->
            <td class="meta-cell">Fecha de Elaboración: <strong>{{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</strong></td>
        </tr>

        <tr>
            <!-- Columna 5 - fila 3: Última Revisión -->
            <td class="meta-cell">Última Revisión: <strong>-</strong></td>
        </tr>

        <tr>
            <!-- Fila 4: en columna 1 va FOR-PIN-01 -->
            <td class="code-cell">FOR-PIN-01</td>

            <!-- Columna 5 - fila 4: Área -->
            <td class="meta-cell">Área: <strong>PINTURA</strong></td>
        </tr>
    </table>

    <!-- Tabla de Métricas (compacta) -->
    <table class="metrics-table" role="presentation">
        <tbody>
            <tr>
                <td style="width:10%;">Fecha</td>
                <td style="width:15%;" class="metrics-value">{{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</td>
                <td style="width:10%;">Turno</td>
                <td style="width:15%;" class="metrics-value">{{ $shift->name }}</td>
                <td style="width:10%;">Hora Inicio</td>
                <td style="width:15%;" class="metrics-value">{{ $shift->start_time }}</td>
                <td style="width:10%;">Hora Fin</td>
                <td style="width:15%;" class="metrics-value">{{ $shift->end_time }}</td>
            </tr>
            <tr>
                <td>Tiempo Efectivo</td>
                <td class="metrics-value">{{ $effectiveProductionTimePerShift ?? '-' }} Min</td>
                <td>Tiempo Paros</td>
                <td class="metrics-value">{{ $totalDowntimeMinutes ?? '-' }} Min</td>
                <td>Total Paros</td>
                <td class="metrics-value">{{ $totalDowntimeCount ?? '-' }}</td>
                <td>Scrap</td>
                <td class="metrics-value">{{ $totalScrap ?? '-' }}</td>
            </tr>
        </tbody>
    </table>

    <!-- Tabla de Producción - MANTENIENDO ESTRUCTURA ORIGINAL -->
    @if (count($records) > 0)
        <table class="production-table" role="grid">
            <thead>
                <tr>
                    <th rowspan="2" width="8%">Estación</th>
                    <th rowspan="2" width="12%">Núm. Parte</th>
                    <th rowspan="2" width="10%">Paq. Estándar</th>
                    <th rowspan="2" width="12%">Modelo</th>
                    <th rowspan="2" width="8%">Cant. Plan</th>
                    <th rowspan="2" width="8%">Cant. Real</th>
                    <th colspan="{{ count($timeHeaders) + 1 }}">REGISTRO POR HORA</th>
                </tr>
                <tr>
                    <th class="sub-header">Tipo</th>
                    @foreach ($timeHeaders as $header)
                        <th class="sub-header" style="width:3%;">{{ $header }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($records as $record)
                    <tr class="part-row part-group-wrapper">
                        <td rowspan="2" class="fixed-column" style="font-family: Arial, sans-serif;">{{ $record['line_name'] }}</td>
                        <td rowspan="2" class="fixed-column" style="font-family: Arial, sans-serif;">{{ $record['part_number'] }}</td>
                        <td rowspan="2" class="fixed-column" style="font-family: Arial, sans-serif;">{{ $record['standard_pack'] }} - {{ $record['standard_pack_quantity'] }}</td>
                        <td rowspan="2" class="fixed-column" style="font-family: Arial, sans-serif;">{{ $record['model'] ?: '-' }}</td>
                        <td rowspan="2" class="fixed-column" style="font-family: Arial, sans-serif;">{{ $record['planned_quantity'] }}</td>
                        <td rowspan="2" class="fixed-column" style="font-family: Arial, sans-serif;">{{ number_format($record['total_entries']) }}</td>

                        <td class="sub-header">Entrada</td>
                        @foreach ($timeHeaders as $header)
                            <td>{{ $record['entries'][$header] !== 0 ? number_format($record['entries'][$header]) : '-' }}</td>
                        @endforeach
                    </tr>
                    <tr class="part-row part-group-wrapper">
                        <td class="sub-header">Salida</td>
                        @foreach ($timeHeaders as $header)
                            <td>{{ $record['exits'][$header] !== 0 ? number_format($record['exits'][$header]) : '-' }}</td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <table role="presentation">
            <tr>
                <td style="text-align:center;padding:20px;background:#f9f9f9;border:1px solid #000;font-family:Arial,sans-serif;">
                    <strong>No hay planes de producción</strong><br>
                    No se encontraron planes para el turno actual.
                </td>
            </tr>
        </table>
    @endif
</body>

</html>
