@extends('adminlte::page')

@section('title', 'Dashboard de Producción')

@section('content_header')
    <h1>Dashboard de Producción</h1>
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
                        <div class="text-primary">
                            <i class="fas fa-clock fa-2x"></i>
                        </div>
                    </div>
                    <h3 class="mt-3 mb-0">{{ $effectiveProductionTime }} Min</h3>
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
                        <div class="text-danger">
                            <i class="fas fa-stopwatch fa-2x"></i>
                        </div>
                    </div>
                    <h3 class="mt-3 mb-0">{{ $totalDowntimeMinutes }} Min</h3>
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
                        <div class="text-warning">
                            <i class="fas fa-exclamation-triangle fa-2x"></i>
                        </div>
                    </div>
                    <h3 class="mt-3 mb-0">{{ $totalDowntimeCount }}</h3>
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
                        <div class="text-danger">
                            <i class="fas fa-trash-alt fa-2x"></i>
                        </div>
                    </div>
                    <h3 class="mt-3 mb-0">{{ $totalScrap }}</h3>
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
                        <div class="text-secondary">
                            <i class="fas fa-paint-roller fa-2x"></i>
                        </div>
                    </div>
                    <h3 class="mt-3 mb-0">{{ $totalHooksUsed }}</h3>
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
    </style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Reemplaza con tu propio kit de FontAwesome -->
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
        });
    </script>
@stop
