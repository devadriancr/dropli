@extends('adminlte::page')

@section('title', 'Validador de Infor')

@section('content_header')
    <div class="d-flex justify-content-between align-items-end flex-wrap gap-3">
        <h1 class="mb-0">{{ __('Validador de Infor') }}</h1>

        <form method="GET" action="{{ route('production-records.infor-validation') }}"
            class="d-flex align-items-end gap-2 flex-wrap header-filter-form">
            <div>
                <label for="start_date" class="form-label fw-500 small text-secondary text-uppercase mb-1">{{ __('Desde') }}</label>
                <input type="date" id="start_date" name="start_date" class="form-control form-control-sm"
                    value="{{ $startDate }}" max="{{ now()->format('Y-m-d') }}" required>
            </div>
            <div>
                <label for="end_date" class="form-label fw-500 small text-secondary text-uppercase mb-1">{{ __('Hasta') }}</label>
                <input type="date" id="end_date" name="end_date" class="form-control form-control-sm"
                    value="{{ $endDate }}" max="{{ now()->format('Y-m-d') }}" required>
            </div>
            <button type="submit" class="btn btn-primary btn-sm rounded-3">
                <span>Consultar</span>
            </button>
        </form>
    </div>
@stop

@section('content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if ($inforError)
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ $inforError }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if ($records !== null)
        <!-- KPIs -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm rounded-3 kpi-card kpi-total">
                    <div class="card-body">
                        <div class="kpi-num">{{ $summary['total'] }}</div>
                        <div class="kpi-lbl">Total en SQL</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm rounded-3 kpi-card kpi-ok">
                    <div class="card-body">
                        <div class="kpi-num">{{ $summary['ok'] }}</div>
                        <div class="kpi-lbl">OK en FSO e ITH</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm rounded-3 kpi-card kpi-dup">
                    <div class="card-body">
                        <div class="kpi-num">{{ $summary['fso_issues'] }}</div>
                        <div class="kpi-lbl">Con problemas en FSO</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm rounded-3 kpi-card kpi-missing">
                    <div class="card-body">
                        <div class="kpi-num">{{ $summary['ith_issues'] }}</div>
                        <div class="kpi-lbl">Con problemas en ITH</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Resultados -->
        <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
            <!-- Header con filtros de estado y buscador -->
            <div class="card-header bg-white border-0 py-3">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <!-- Filtros por estado -->
                    <div class="btn-group" role="group" aria-label="Filtro de estado">
                        <button type="button" class="btn btn-outline-secondary btn-sm status-filter-btn active" data-filter="all">
                            Todos ({{ $summary['total'] }})
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm status-filter-btn" data-filter="ok">
                            OK ({{ $summary['ok'] }})
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm status-filter-btn" data-filter="issues">
                            Con problemas ({{ $summary['total'] - $summary['ok'] }})
                        </button>
                    </div>

                    <!-- Buscador -->
                    <div class="search-box">
                        <div class="input-group">
                            <input type="text" id="tableSearch" class="form-control border-end-0"
                                placeholder="Buscar centro, parte, orden..." aria-label="Buscar">
                            <span class="input-group-text bg-white border-start-0">
                                <i class="fas fa-search text-secondary"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cuerpo con tabla -->
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="resultsTable">
                        <thead class="bg-light sticky-top">
                            <tr>
                                <th class="fw-bold text-secondary text-uppercase border-light-subtle">{{ __('Centro de Trabajo') }}</th>
                                <th class="fw-bold text-secondary text-uppercase border-light-subtle">{{ __('Número de Parte') }}</th>
                                <th class="fw-bold text-secondary text-uppercase border-light-subtle">{{ __('Número de Orden') }}</th>
                                <th class="fw-bold text-secondary text-uppercase border-light-subtle">{{ __('Fecha Plan.') }}</th>
                                <th class="fw-bold text-secondary text-uppercase border-light-subtle">{{ __('Turno') }}</th>
                                <th class="fw-bold text-secondary text-uppercase border-light-subtle">{{ __('Cant. Planeada') }}</th>
                                <th class="fw-bold text-secondary text-uppercase border-light-subtle">{{ __('Cant. Producida') }}</th>
                                <th class="fw-bold text-secondary text-uppercase border-light-subtle text-center">{{ __('FSO') }}</th>
                                <th class="fw-bold text-secondary text-uppercase border-light-subtle text-center">{{ __('ITH') }}</th>
                                <th class="fw-bold text-secondary text-uppercase border-light-subtle">{{ __('Sincronizado') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($records as $record)
                                @php
                                    $searchable = strtolower(implode(' ', array_filter([
                                        $record['work_center_number'],
                                        $record['work_center_name'],
                                        $record['part_number'],
                                        $record['part_name'],
                                        $record['order_number'],
                                        $record['shift'],
                                    ])));
                                @endphp
                                <tr class="border-light-subtle" data-status="{{ $record['overall_status'] }}" data-search="{{ $searchable }}">
                                    <td class="py-3">
                                        <div class="fw-500">{{ $record['work_center_number'] }}</div>
                                        <div class="text-muted small">{{ $record['work_center_name'] ?? '-' }}</div>
                                    </td>
                                    <td class="py-3">
                                        <div class="fw-500">{{ $record['part_number'] }}</div>
                                        <div class="text-muted small">{{ $record['part_name'] ?? '-' }}</div>
                                    </td>
                                    <td class="py-3">{{ $record['order_number'] ?? '-' }}</td>
                                    <td class="py-3 small">{{ \Carbon\Carbon::parse($record['planned_date'])->format('Y-m-d') }}</td>
                                    <td class="py-3">
                                        <span class="badge-status bg-light bg-opacity-10 text-info">{{ $record['shift'] ?: '-' }}</span>
                                    </td>
                                    <td class="py-3">
                                        <span class="badge-status bg-primary bg-opacity-10 text-primary">{{ $record['planned_quantity'] }}</span>
                                    </td>
                                    <td class="py-3">
                                        <span class="badge-status bg-success bg-opacity-10 text-success">{{ $record['produced_quantity'] }}</span>
                                    </td>
                                    <td class="py-3 text-center">
                                        @switch($record['fso_status'])
                                            @case('ok')
                                                <span class="badge-status bg-success bg-opacity-10 text-success">OK</span>
                                                @break
                                            @case('duplicate')
                                                <span class="badge-status bg-warning bg-opacity-10 text-warning" title="Órdenes: {{ $record['fso_orders'] }}">Duplicado x{{ $record['fso_count'] }}</span>
                                                @break
                                            @default
                                                <span class="badge-status bg-danger bg-opacity-10 text-danger">NG</span>
                                                @if ($record['fso_registered_quantities'])
                                                    <div class="text-muted small mt-1" title="Cantidad registrada en FSO">
                                                        FSO: {{ $record['fso_registered_quantities'] }}
                                                    </div>
                                                @endif
                                        @endswitch
                                    </td>
                                    <td class="py-3 text-center">
                                        @switch($record['ith_status'])
                                            @case('ok')
                                                <span class="badge-status bg-success bg-opacity-10 text-success">OK</span>
                                                @break
                                            @case('duplicate')
                                                <span class="badge-status bg-warning bg-opacity-10 text-warning" title="Órdenes: {{ $record['ith_orders'] }}">Duplicado x{{ $record['ith_count'] }}</span>
                                                @break
                                            @case('missing')
                                                <span class="badge-status bg-danger bg-opacity-10 text-danger">NG</span>
                                                @break
                                            @default
                                                <span class="badge-status bg-secondary bg-opacity-10 text-secondary" title="No se encontró la orden en FSO, no se pudo verificar en ITH">No verificado</span>
                                        @endswitch
                                    </td>
                                    <td class="py-3 small">
                                        {{ $record['synced_at'] ? \Carbon\Carbon::parse($record['synced_at'])->format('Y-m-d H:i:s') : '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center py-4">
                                        <div class="d-flex flex-column align-items-center">
                                            <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                                            <span class="text-secondary">No se encontraron registros terminados y sincronizados con Infor en ese rango de fechas</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    <div id="noResultsRow" class="text-center py-4 d-none">
                        <div class="d-flex flex-column align-items-center">
                            <i class="fas fa-search fa-2x text-muted mb-2"></i>
                            <span class="text-secondary">Sin resultados para ese filtro o búsqueda</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pie con conteo de resultados -->
            <div class="card-footer bg-white border-0 py-3">
                <div class="text-muted small" id="resultsCountText">
                    Mostrando {{ $summary['total'] }} de {{ $summary['total'] }} resultados
                </div>
            </div>
        </div>
    @else
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body text-center py-5">
                <i class="fas fa-satellite-dish fa-2x text-muted mb-3"></i>
                <p class="text-secondary mb-0">Selecciona un rango de fechas y presiona Consultar para validar los registros terminados contra FSO e ITH en Infor.</p>
            </div>
        </div>
    @endif
@stop

@section('css')
    <!-- Fuente Google Roboto -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">

    <style>
        .card,
        .btn,
        .form-control,
        .table,
        .content-header h1 {
            font-family: 'Roboto', sans-serif !important;
        }

        .border-light-subtle {
            border-color: #f0f0f0 !important;
        }

        .table-hover tbody tr:hover {
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            transform: translateY(-1px);
            transition: all 0.2s ease;
        }

        .rounded-3 {
            border-radius: 12px !important;
        }

        .table thead th {
            font-weight: 700 !important;
            font-size: 0.85rem;
        }

        .table tbody td {
            font-size: 0.875rem;
        }

        .badge-status {
            display: inline-block;
            min-width: 60px;
            padding: 0.5em 0.75em;
            text-align: center;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .fw-500 {
            font-weight: 500;
        }

        .alert {
            border-radius: 8px;
        }

        .btn-close {
            background-size: 0.75rem;
            padding: 0.5rem;
        }

        /* Filtro de fechas en el renglón del título */
        .header-filter-form input[type="date"] {
            width: 155px;
        }

        /* Buscador estilo pill, igual al resto del módulo */
        .search-box .input-group {
            width: 300px;
        }

        .search-box .form-control {
            border-radius: 20px 0 0 20px !important;
            border-right: none;
            padding: 0.5rem 1.25rem;
            height: 40px;
            font-size: 0.9rem;
        }

        .search-box .input-group-text {
            border-radius: 0 20px 20px 0 !important;
            border-left: none;
            background-color: white;
            padding: 0 1.1rem;
        }

        .search-box .form-control:focus {
            border-color: #dee2e6 !important;
            box-shadow: none !important;
            outline: none !important;
        }

        /* Filtros por estado */
        .status-filter-btn.active {
            background-color: #6c757d;
            border-color: #6c757d;
            color: #fff;
        }

        /* KPI cards con borde superior de color */
        .kpi-card .card-body {
            border-top: 4px solid transparent;
            padding: 1.25rem;
        }

        .kpi-num {
            font-size: 1.9rem;
            font-weight: 700;
            line-height: 1.1;
        }

        .kpi-lbl {
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #6c757d;
            margin-top: 4px;
        }

        .kpi-total .card-body {
            border-top-color: #1a73e8;
        }

        .kpi-ok .card-body {
            border-top-color: #188038;
        }

        .kpi-dup .card-body {
            border-top-color: #f9ab00;
        }

        .kpi-missing .card-body {
            border-top-color: #d93025;
        }
    </style>
@stop

@section('js')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(() => {
                const alerts = document.querySelectorAll('.alert');
                alerts.forEach(alert => {
                    if (typeof bootstrap !== 'undefined' && bootstrap.Alert) {
                        const bsAlert = new bootstrap.Alert(alert);
                        bsAlert.close();
                    }
                });
            }, 5000);

            const searchInput = document.getElementById('tableSearch');
            const filterButtons = document.querySelectorAll('.status-filter-btn');
            const rows = document.querySelectorAll('#resultsTable tbody tr[data-status]');
            const noResultsRow = document.getElementById('noResultsRow');
            const resultsCountText = document.getElementById('resultsCountText');
            const totalRows = rows.length;
            let currentFilter = 'all';

            function applyFilters() {
                const term = (searchInput?.value || '').toLowerCase().trim();
                let visibleCount = 0;

                rows.forEach(function(row) {
                    const matchesStatus = currentFilter === 'all' || row.dataset.status === currentFilter;
                    const matchesSearch = !term || row.dataset.search.includes(term);
                    const show = matchesStatus && matchesSearch;
                    row.style.display = show ? '' : 'none';
                    if (show) visibleCount++;
                });

                if (noResultsRow) {
                    noResultsRow.classList.toggle('d-none', totalRows === 0 || visibleCount > 0);
                }

                if (resultsCountText) {
                    resultsCountText.textContent = 'Mostrando ' + visibleCount + ' de ' + totalRows + ' resultados';
                }
            }

            filterButtons.forEach(function(btn) {
                btn.addEventListener('click', function() {
                    filterButtons.forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    currentFilter = this.dataset.filter;
                    applyFilters();
                });
            });

            searchInput?.addEventListener('input', applyFilters);
        });
    </script>
@stop
