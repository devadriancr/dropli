<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro Diario de Producción</title>

    <style>
        @page {
            size: letter landscape;
            margin: 15mm;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: Arial, Helvetica, sans-serif !important;
        }

        body {
            color: #222;
            font-size: 8px;
            padding: 12px;
            font-family: Arial, Helvetica, sans-serif !important;
        }

        /* ===== TABLAS CON BORDES REDONDEADOS ===== */
        .card-table {
            width: 100%;
            border: 0.5px solid #666;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 10px;
        }

        .card-table table {
            width: 100%;
            border-collapse: collapse;
            border-spacing: 0;
        }

        .card-table th,
        .card-table td {
            padding: 5px 6px;
            border: 0.5px solid #666;
            font-size: 8px;
            vertical-align: middle;
            text-align: center;
            font-family: Arial, Helvetica, sans-serif !important;
        }

        /* ===== TABLA INTERNA (sin bordes) ===== */
        .inner-table {
            width: 100%;
            border-collapse: collapse;
        }

        .inner-table td {
            border: none;
            padding: 2px 4px;
            font-size: 8px;
            vertical-align: middle;
            font-family: Arial, Helvetica, sans-serif !important;
        }

        .inner-table td:first-child {
            font-weight: 700;
            text-align: left;
            width: 70%;
        }

        .inner-table td:last-child {
            font-weight: 400;
            text-align: right;
            width: 30%;
            padding-right: 8px;
        }

        /* ===== ESTILOS DEL ENCABEZADO ===== */
        .code-cell {
            text-align: center !important;
            padding: 6px !important;
            font-weight: 700;
        }

        .header-title {
            font-weight: 700;
            font-size: 12px;
            letter-spacing: 0.5px;
            text-align: center;
        }

        .header-logo {
            width: 85px;
            display: block;
            margin: 0 auto;
        }

        /* ===== TABLA DE MÉTRICAS ===== */
        .metrics-table td {
            padding: 6px;
        }

        .metric-label {
            background-color: #f5f5f5;
            font-weight: 700;
        }

        /* ===== TABLA DE PRODUCCIÓN CON BORDES DELGADOS Y REDONDEADOS ===== */
        .production-wrapper {
            border: 0.5px solid #666;
            border-radius: 8px;
            overflow: hidden;
            margin-top: 6px;
        }

        .production-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            font-family: Arial, Helvetica, sans-serif !important;
        }

        .production-table thead th {
            background-color: #e8e8e8;
            font-weight: 700;
            text-align: center;
            padding: 6px 4px;
            font-size: 8px;
            border: 0.5px solid #666;
            vertical-align: middle;
            font-family: Arial, Helvetica, sans-serif !important;
        }

        .production-table tbody td {
            text-align: center;
            padding: 5px 3px;
            font-size: 8px;
            border: 0.5px solid #666;
            vertical-align: middle;
            white-space: normal;
            font-weight: 400 !important;
            font-family: Arial, Helvetica, sans-serif !important;
        }

        /* Columnas fijas */
        .production-table .fixed-column {
            background-color: #fafafa;
            text-align: left;
            font-weight: 400 !important;
            padding-left: 8px;
            border: 0.5px solid #666;
            white-space: normal;
            word-break: break-word;
            font-family: Arial, Helvetica, sans-serif !important;
        }

        /* ===== COLUMNA TIPO (Entrada/Salida) ===== */
        .type-cell {
            padding: 6px 4px;
            vertical-align: middle;
            white-space: normal;
            font-family: Arial, Helvetica, sans-serif !important;
        }

        .type-stack {
            display: inline-block;
            text-align: center;
            line-height: 1;
        }

        .type-stack .type-entry,
        .type-stack .type-exit {
            display: block;
            font-weight: 400 !important;
            font-size: 8px;
            margin-bottom: 2px;
            color: #222;
            font-family: Arial, Helvetica, sans-serif !important;
        }

        /* ===== CELDAS HORARIAS ===== */
        .hour-cell,
        .production-table th.hour-header {
            width: 38px;
            max-width: 38px;
            min-width: 28px;
            padding: 4px 2px;
            white-space: normal;
            font-family: Arial, Helvetica, sans-serif !important;
        }

        .cell-stack {
            display: block;
            line-height: 1;
            width: 100%;
            font-family: Arial, Helvetica, sans-serif !important;
        }

        .cell-stack .entry-val,
        .cell-stack .exit-val {
            display: block;
            font-weight: 400 !important;
            font-size: 8px;
            color: #222;
            font-family: Arial, Helvetica, sans-serif !important;
        }

        /* ===== UTILIDADES ===== */
        .part-group-wrapper {
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .empty-state {
            text-align: center;
            padding: 20px;
            background: #f9f9f9;
            border: 0.5px solid #666;
        }

        @media print {
            body {
                font-size: 8px;
            }

            .production-table td,
            .production-table th {
                font-size: 8px;
            }
        }
    </style>

</head>

<body>
    <!-- ===== ENCABEZADO ===== -->
    <div class="card-table">
        <table>
            <tbody>
                <tr>
                    <td rowspan="3" style="width: 20%;">
                        <img src="{{ public_path('images/ykm.png') }}" alt="Logo YKM" class="header-logo">
                    </td>

                    <td colspan="4" rowspan="4" class="header-title"
                        style="vertical-align: middle; text-align: center;">
                        REGISTRO DIARIO DE PRODUCCIÓN
                    </td>

                    <td>
                        <table class="inner-table">
                            <tr>
                                <td>REVISIÓN</td>
                                <td>1</td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td>
                        <table class="inner-table">
                            <tr>
                                <td>FECHA DE ELABORACIÓN</td>
                                <td>17-JULIO-2023</td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td>
                        <table class="inner-table">
                            <tr>
                                <td>ÚLTIMA REVISIÓN</td>
                                <td>17-JULIO-2023</td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td class="code-cell">FOR-PIN-01</td>
                    <td>
                        <table class="inner-table">
                            <tr>
                                <td>ÁREA</td>
                                <td>PINTURA</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- ===== MÉTRICAS ===== -->
    <div class="card-table metrics-table">
        <table>
            <tbody>
                <tr>
                    <td class="metric-label">Fecha</td>
                    <td class="metric-value">{{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</td>
                    <td class="metric-label">Hora de Inicio de Producción</td>
                    <td class="metric-value">{{ $shift->start_time }}</td>
                    <td class="metric-label">Total de Ganchos Primarios Utilizados</td>
                    <td class="metric-value">{{ $totalHooksUsedPerShift ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="metric-label">Turno</td>
                    <td class="metric-value">{{ $shift->name }}</td>
                    <td class="metric-label">Hora de Termino de Producción</td>
                    <td class="metric-value">{{ $shift->end_time }}</td>
                    <td class="metric-label">Tasa de Colgado por Turno</td>
                    <td class="metric-value">{{ $hangingRatePerShift ?? '-' }} %</td>
                </tr>
                <tr>
                    <td class="metric-label">Total de Piezas de Scrap</td>
                    <td class="metric-value">{{ $totalScrap ?? '-' }}</td>
                    <td class="metric-label">Tiempo Efectivo de Producción</td>
                    <td class="metric-value">{{ $effectiveProductionTimePerShift ?? '-' }} Min</td>
                    <td class="metric-label">JPH Promedio por Turno</td>
                    <td class="metric-value">{{ $averageJphPerShift ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="metric-label">Total de Paros</td>
                    <td class="metric-value">{{ $totalDowntimeCount ?? '-' }}</td>
                    <td class="metric-label">Tiempo Total de Paros</td>
                    <td class="metric-value">{{ $totalDowntimeCount ?? '0' }} Min</td>
                    <td class="metric-label"></td>
                    <td class="metric-value"></td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- ===== TABLA DE PRODUCCIÓN ===== -->
    <div class="production-wrapper">
        <table class="production-table">
            <thead>
                <tr>
                    <th style="width:6%;">No. Order</th>
                    <th style="width:8%;">Estación</th>
                    <th style="width:8%;">Modelo</th>
                    <th style="width:8%;">Núm. Parte</th>
                    <th style="width:6%;">Cant. Plan</th>
                    <th style="width:6%;">Cant. Real</th>
                    <th style="width:6%;"></th>
                    @foreach ($timeHeaders as $header)
                        <th class="hour-header">{{ $header }}</th>
                    @endforeach
                </tr>
            </thead>

            <tbody>
                @foreach ($records as $record)
                    <tr class="part-group-wrapper record-group">
                        <td class="fixed-column">{{ $record['order_number'] }}</td>
                        <td class="fixed-column">{{ $record['line_name'] }}</td>
                        <td class="fixed-column">{{ $record['model'] ?: '-' }}</td>
                        <td class="fixed-column">{{ $record['part_number'] }}</td>
                        <td class="fixed-column">{{ $record['planned_quantity'] ?? '-' }}</td>
                        <td class="fixed-column">{{ number_format($record['total_entries'] ?? 0) }}</td>

                        <td class="type-cell">
                            <div class="type-stack">
                                <span class="type-entry">Entrada</span>
                                <span class="type-exit">Salida</span>
                            </div>
                        </td>

                        @foreach ($timeHeaders as $header)
                            @php
                                $entry = $record['entries'][$header] ?? null;
                                $exit = $record['exits'][$header] ?? null;
                            @endphp

                            <td class="hour-cell">
                                <div class="cell-stack">
                                    <span class="entry-val">
                                        @if (is_numeric($entry) && $entry != 0)
                                            {{ number_format($entry) }}
                                        @elseif ($entry === 0)
                                            0
                                        @else
                                            -
                                        @endif
                                    </span>

                                    <span class="exit-val">
                                        @if (is_numeric($exit) && $exit != 0)
                                            {{ number_format($exit) }}
                                        @elseif ($exit === 0)
                                            0
                                        @else
                                            -
                                        @endif
                                    </span>
                                </div>
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>

</html>
