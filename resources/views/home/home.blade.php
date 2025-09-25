@extends('adminlte::page')

@section('title', 'Dashboard de Producción')

@section('content_header')
    <div class="row">
        <div class="col-md-6">
            <h1>Dashboard de Producción</h1>
        </div>
        <div class="col-md-6">
            <div class="float-right">
                <form method="GET" action="{{ route('home.index') }}" class="form-inline">
                    <div class="form-group mr-2 mb-2">
                        <label for="date" class="mr-2">Fecha:</label>
                        <input type="date"
                               id="date"
                               name="date"
                               value="{{ $selectedDate }}"
                               max="{{ \Carbon\Carbon::today()->format('Y-m-d') }}"
                               class="form-control form-control-sm">
                    </div>

                    <div class="form-group mr-2 mb-2">
                        <label for="shift_id" class="mr-2">Turno:</label>
                        <select id="shift_id" name="shift_id" class="form-control form-control-sm">
                            @foreach($shifts as $s)
                                <option value="{{ $s->id }}"
                                        {{ $selectedShiftId == $s->id ? 'selected' : '' }}>
                                    {{ $s->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary btn-sm mr-2 mb-2">
                        <i class="fas fa-filter"></i> Filtrar
                    </button>

                    <a href="{{ route('home.index') }}" class="btn btn-secondary btn-sm mb-2">
                        <i class="fas fa-sync"></i>
                    </a>
                </form>
            </div>
        </div>
    </div>
@stop

@section('content')
    <!-- Cards con métricas simplificadas -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-1">Tiempo Efectivo de Producción</h5>
                        </div>
                        <div class="text-blue">
                            <i class="fas fa-clock fa-2x"></i>
                        </div>
                    </div>
                    <h3 class="mt-3 mb-0">{{ $effectiveProductionTimePerShift ?? '-' }} Min</h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-1">Tiempo Total de Paros</h5>
                        </div>
                        <div class="text-red">
                            <i class="fas fa-ban fa-2x"></i>
                        </div>
                    </div>
                    <h3 class="mt-3 mb-0">{{ $totalDowntimeMinutes ?? '-' }} Min</h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-1">Total de Paros</h5>
                        </div>
                        <div class="text-red">
                            <i class="fas fa-exclamation-triangle fa-2x"></i>
                        </div>
                    </div>
                    <h3 class="mt-3 mb-0">{{ $totalDowntimeCount ?? '-' }}</h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-1">Total de Piezas de Scrap</h5>
                        </div>
                        <div class="text-red">
                            <i class="fas fa-trash-alt fa-2x"></i>
                        </div>
                    </div>
                    <h3 class="mt-3 mb-0">{{ $totalScrap ?? '-' }}</h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-1">Total de Ganchos Primarios Utilizados</h5>
                        </div>
                        <div class="text-green">
                            <i class="fas fa-paint-roller fa-2x"></i>
                        </div>
                    </div>
                    <h3 class="mt-3 mb-0">{{ $totalHooksUsedPerShift ?? '-' }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-1">Tasa de Colgado por Turno</h5>
                        </div>
                        <div class="text-green">
                            <i class="fas fa-chart-line fa-2x"></i>
                        </div>
                    </div>
                    <h3 class="mt-3 mb-0">{{ $hangingRatePerShift ?? '-' }} %</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-1">JPH Promedio por Turno</h5>
                        </div>
                        <div class="text-green">
                            <i class="fas fa-chart-bar fa-2x"></i>
                        </div>
                    </div>
                    <h3 class="mt-3 mb-0">{{ $averageJphPerShift ?? '-' }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráfica de producción -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"> {{ $date }} - Turno {{ $shift->name }}</h3>
                </div>
                <div class="card-body">
                    <div class="chart">
                        <canvas id="productionChart" style="min-height: 600px; height: 600px; max-height: 600px; max-width: 100%;"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .card {
            border: 1px solid #eaeaea;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            transition: transform 0.2s;
        }
        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .card-title {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        .card-text {
            font-size: 0.85rem;
        }
        h3 {
            font-weight: 700;
            color: #333;
        }
        .form-inline .form-group {
            display: flex;
            align-items: center;
            margin-right: 0.5rem;
        }
        .form-inline label {
            margin-right: 0.5rem;
            margin-bottom: 0;
            font-weight: 500;
            color: #555;
        }
        .float-right {
            float: right;
        }
        .text-blue { color: #007bff; }
        .text-red { color: #dc3545; }
        .text-green { color: #28a745; }

        /* Responsive */
        @media (max-width: 768px) {
            .float-right {
                float: none !important;
                margin-top: 15px;
            }
            .form-inline .form-group {
                margin-right: 0;
                margin-bottom: 10px;
                width: 100%;
            }
            .form-inline .form-group label {
                min-width: 60px;
            }
            .btn {
                width: 100%;
                margin-bottom: 5px;
            }
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var ctx = document.getElementById('productionChart').getContext('2d');
            var productionChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: @json($labels),
                    datasets: [
                        {
                            label: 'Planeado',
                            data: @json($data['planned']),
                            backgroundColor: 'rgba(142, 197, 255)',
                            borderColor: 'rgba(20, 71, 230)',
                            borderWidth: 2,
                            borderRadius: 2,
                        },
                        {
                            label: 'Producido',
                            data: @json($data['produced']),
                            backgroundColor: 'rgba(123, 241, 168)',
                            borderColor: 'rgba(0, 130, 54)',
                            borderWidth: 2,
                            borderRadius: 2,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Cantidad'
                            }
                        },
                    },
                }
            });

            // Agregar funcionalidad para limpiar filtros
            document.getElementById('clearFilters').addEventListener('click', function() {
                document.getElementById('date').value = '';
                document.getElementById('shift_id').selectedIndex = 0;
            });
        });
    </script>
@stop
