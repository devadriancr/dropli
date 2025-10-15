@extends('adminlte::page')

@section('title', 'Registros de Producción')

@section('content_header')
    <h1>{{ __('Registros de Producción') }}</h1>
@stop

@section('content')
    {{-- Agregamos las alertas que faltaban --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <!-- Header con buscador -->
        <div class="card-header bg-white border-0 py-3">
            <div class="d-flex justify-content-between align-items-center">
                <!-- Buscador -->
                <div class="search-box">
                    <form method="GET" action="{{ route('production-records.index') }}">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control border-end-0" placeholder="Buscar..."
                                aria-label="Buscar" value="{{ $search ?? '' }}">
                            <button type="submit" class="input-group-text bg-white border-start-0">
                                <i class="fas fa-search text-secondary"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Cuerpo con tabla -->
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light sticky-top">
                        <tr>
                            <th class="fw-bold text-secondary text-uppercase border-light-subtle">
                                {{ __('Centro de Trabajo') }}</th>
                            <th class="fw-bold text-secondary text-uppercase border-light-subtle">
                                {{ __('Número de Parte') }}</th>
                            <th class="fw-bold text-secondary text-uppercase border-light-subtle">{{ __('Secuencia') }}</th>
                            <th class="fw-bold text-secondary text-uppercase border-light-subtle">{{ __('Cantidad') }}</th>
                            <th class="fw-bold text-secondary text-uppercase border-light-subtle">
                                {{ __('Entrada') }} / {{ __('Salida') }}</th>
                            <th class="fw-bold text-secondary text-uppercase border-light-subtle">
                                {{ __('Fecha de Creación') }}</th>
                            <th class="fw-bold text-secondary text-uppercase border-light-subtle text-center">
                                {{ __('Acciones') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($productionRecords as $record)
                            <tr
                                class="border-light-subtle record-row
                                @if ($record->record_type == 'entry') record-entry
                                @elseif($record->record_type == 'exit') record-exit @endif">
                                <!-- Centro de Trabajo -->
                                <td class="py-3">
                                    @if ($record->partNumber && $record->partNumber->workCenter)
                                        <div class="fw-500">{{ $record->partNumber->workCenter->number }}</div>
                                        <div class="text-muted small">{{ $record->partNumber->workCenter->name }}</div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>

                                <!-- Número de Parte -->
                                <td class="py-3">
                                    @if ($record->partNumber)
                                        <div class="fw-500">{{ $record->partNumber->number }}</div>
                                        <div class="text-muted small">{{ $record->partNumber->name ?? '-' }}</div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>

                                <!-- Secuencia -->
                                <td class="py-3">
                                    @if (isset($record->sequence))
                                        <span class="badge-status bg-secondary bg-opacity-10 text-secondary">
                                            {{ $record->sequence }}
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>

                                <!-- Cantidad -->
                                <td class="py-3">
                                    <span class="badge-status bg-secondary bg-opacity-10 text-secondary">
                                        {{ $record->quantity ?? '-' }}
                                    </span>
                                </td>

                                <!-- Tipo de Registro -->
                                <td class="py-3">
                                    @if ($record->record_type == 'exit')
                                        <span class="badge-status bg-success bg-opacity-10 text-success">
                                            {{ __('Salida') }}
                                        </span>
                                    @elseif ($record->record_type == 'entry')
                                        <span class="badge-status bg-primary bg-opacity-10 text-primary">
                                            {{ __('Entrada') }}
                                        </span>
                                    @else
                                        <span class="badge-status bg-secondary bg-opacity-10 text-secondary">
                                            -
                                        </span>
                                    @endif
                                </td>

                                <!-- Fecha de Creación -->
                                <td class="py-3 small">
                                    <div class="fw-500">{{ $record->created_at->format('Y-m-d') }}</div>
                                    <div class="text-muted">{{ $record->created_at->format('H:i:s') }}</div>
                                </td>

                                <!-- Acciones -->
                                <td class="py-3 text-center">
                                    <div class="d-flex justify-content-center gap-2 align-items-center">
                                        @if ($record->productionPlan->synced_to_infor === false)
                                            <a href="{{ route('production-records.edit', $record) }}"
                                                class="btn btn-sm btn-outline-primary rounded-3">
                                                <i class="fas fa-edit me-1"></i>
                                                <span>Editar</span>
                                            </a>
                                        @else
                                            <span class="text-success small fw-500">
                                                <i class="fas fa-check-circle me-1"></i>
                                                Registrado en Infor
                                            </span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="fas fa-clipboard-list fa-2x text-muted mb-2"></i>
                                        <span class="text-secondary">No se encontraron registros de producción</span>
                                        @if (!empty($search))
                                            <a href="{{ route('production-records.index') }}"
                                                class="btn btn-sm btn-link mt-2">
                                                Limpiar búsqueda
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pie de página con paginación -->
        @if ($productionRecords->hasPages() || $productionRecords->total() > 0)
            <div class="card-footer bg-white border-0 py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <!-- Información de resultados -->
                    <div class="text-muted small">
                        Mostrando {{ $productionRecords->firstItem() ?? 0 }} a {{ $productionRecords->lastItem() ?? 0 }}
                        de
                        {{ $productionRecords->total() }} resultados
                    </div>

                    <!-- Controles de paginación -->
                    @if ($productionRecords->hasPages())
                        {{ $productionRecords->links('pagination::bootstrap-4') }}
                    @endif
                </div>
            </div>
        @endif
    </div>
@stop

@section('css')
    <!-- Fuente Google Roboto -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">

    <style>
        /* Tus estilos CSS existentes... */
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

        .table-hover tbody tr {
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

        .search-box .input-group {
            width: 380px;
        }

        .search-box .form-control {
            border-radius: 20px 0 0 20px !important;
            border-right: none;
            padding: 0.5rem 1.5rem;
            height: 42px;
            font-size: 0.95rem;
        }

        .search-box .input-group-text {
            border-radius: 0 20px 20px 0 !important;
            border-left: none;
            background-color: white;
            padding: 0 1.25rem;
            font-size: 1rem;
        }

        .search-box .form-control:focus {
            border-color: #dee2e6 !important;
            box-shadow: none !important;
            outline: none !important;
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

        .pagination {
            margin-bottom: 0;
        }

        .page-item .page-link {
            border-radius: 8px;
            margin: 0 3px;
            border: none;
            color: #6c757d;
            font-size: 0.9rem;
            min-width: 32px;
            text-align: center;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
            padding: 6px 12px;
        }

        .page-item.active .page-link {
            background-color: #1a73e8;
            color: white;
        }

        .page-item:not(.active) .page-link:hover {
            background-color: #f8f9fa;
            color: #1a73e8;
        }

        .page-item.disabled .page-link {
            opacity: 0.5;
        }

        .text-muted.small {
            font-size: 0.85rem;
            color: #6c757d;
        }

        .card-footer .pagination {
            margin-bottom: 0;
        }

        .alert {
            border-radius: 8px;
        }

        .btn-close {
            background-size: 0.75rem;
            padding: 0.5rem;
        }

        .fw-500 {
            font-weight: 500;
        }

        /* ===== ESTILOS PARA EL HOVER DE LAS FILAS ===== */
        .record-row {
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .record-entry:hover {
            background-color: rgba(13, 110, 253, 0.08) !important;
            box-shadow: 0 2px 8px rgba(13, 110, 253, 0.1);
            transform: translateY(-1px);
        }

        .record-exit:hover {
            background-color: rgba(25, 135, 84, 0.08) !important;
            box-shadow: 0 2px 8px rgba(25, 135, 84, 0.1);
            transform: translateY(-1px);
        }

        .table-hover tbody tr:hover:not(.record-entry):not(.record-exit) {
            background-color: rgba(108, 117, 125, 0.08) !important;
            box-shadow: 0 2px 8px rgba(108, 117, 125, 0.1);
            transform: translateY(-1px);
        }

        /* Estilos para los botones de acción */
        .btn-sm {
            padding: 0.35rem 0.75rem;
            font-size: 0.85rem;
            /* display: inline-flex; */
            /* align-items: center; */
        }

        .gap-2 {
            gap: 0.5rem;
        }
    </style>
@stop

@section('js')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Cerrar alertas automáticamente después de 5 segundos
            setTimeout(() => {
                const alerts = document.querySelectorAll('.alert');
                alerts.forEach(alert => {
                    if (typeof bootstrap !== 'undefined' && bootstrap.Alert) {
                        const bsAlert = new bootstrap.Alert(alert);
                        bsAlert.close();
                    }
                });
            }, 5000);
        });
    </script>
@stop
