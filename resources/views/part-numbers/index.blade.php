@extends('adminlte::page')

@section('title', 'Números de Parte')

@section('content_header')
    <h1>{{ __('Números de Parte') }}</h1>
@stop

@section('content')
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <!-- Header con buscador -->
        <div class="card-header bg-white border-0 py-3">
            <div class="search-box">
                <form method="GET" action="{{ route('part-numbers.index') }}">
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

        <!-- Cuerpo con tabla -->
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="fw-bold text-secondary text-uppercase">{{ __('Departamento') }}</th>
                            <th class="fw-bold text-secondary text-uppercase">{{ __('Área') }}</th>
                            <th class="fw-bold text-secondary text-uppercase">{{ __('Centro de Trabajo') }}</th>
                            <th class="fw-bold text-secondary text-uppercase">{{ __('Número de Parte') }}</th>
                            <th class="fw-bold text-secondary text-uppercase">{{ __('Clase') }}</th>
                            <th class="fw-bold text-secondary text-uppercase">{{ __('Empaque Estándar') }}</th>
                            <th class="fw-bold text-secondary text-uppercase">{{ __('Cantidad') }}</th>
                            <th class="fw-bold text-secondary text-uppercase">{{ __('Estado') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($partNumbers as $partNumber)
                            <tr>
                                <!-- Departamento -->
                                <td class="py-3">
                                    @if ($partNumber->workCenter && $partNumber->workCenter->area && $partNumber->workCenter->area->department)
                                        <span class="badge-status bg-primary bg-opacity-10 text-primary">
                                            {{ $partNumber->workCenter->area->department->name }}
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>

                                <!-- Área -->
                                <td class="py-3 small">
                                    @if ($partNumber->workCenter && $partNumber->workCenter->area)
                                        <span class="badge-status bg-secondary bg-opacity-10 text-secondary">
                                            {{ $partNumber->workCenter->area->name ?? '-' }}
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>

                                <!-- Centro de Trabajo -->
                                <td class="py-3 small">
                                    @if ($partNumber->workCenter)
                                        <div class="fw-500">{{ $partNumber->workCenter->number }}</div>
                                        <div class="text-muted">{{ $partNumber->workCenter->name }}</div>
                                    @else
                                        -
                                    @endif
                                </td>

                                <!-- Número de Parte -->
                                <td class="py-3 small">
                                    <div class="fw-500">{{ $partNumber->number }}</div>
                                    <div class="text-muted">{{ $partNumber->name }}</div>
                                </td>

                                <!-- Clase -->
                                <td class="py-3 small">
                                    {{ $partNumber->itemClass->abbreviation ?? '-' }}
                                </td>

                                <!-- Empaque Estándar -->
                                <td class="py-3 small">
                                    {{ $partNumber->standardPack->name ?? '-' }}
                                </td>

                                <!-- Cantidad -->
                                <td class="py-3 small">
                                    {{ $partNumber->standard_pack_quantity ?? '-' }}
                                </td>

                                <!-- Estado -->
                                <td class="py-3 small">
                                    @if ($partNumber->is_obsolete)
                                        <span class="badge-status bg-danger bg-opacity-10 text-danger">
                                            <i class="fas fa-times-circle"></i> Obsoleto
                                        </span>
                                    @else
                                        <span class="badge-status bg-success bg-opacity-10 text-success">
                                            <i class="fas fa-check-circle"></i> Activo
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                                        <span class="text-secondary">No se encontraron resultados</span>
                                        @if (!empty($search))
                                            <a href="{{ route('part-numbers.index') }}" class="btn btn-sm btn-link mt-2">
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
        @if ($partNumbers->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted small">
                        Mostrando {{ $partNumbers->firstItem() }} a {{ $partNumbers->lastItem() }} de
                        {{ $partNumbers->total() }} resultados
                    </div>

                    {{ $partNumbers->links('pagination::bootstrap-4') }}
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
        /* Aplicar fuente Roboto a todo el sistema */
        body,
        .main-header,
        .main-sidebar,
        .content-wrapper,
        .card,
        .btn,
        .form-control,
        .table,
        h1,
        h2,
        h3,
        h4,
        h5,
        h6,
        .badge-status,
        .search-box,
        .pagination {
            font-family: 'Roboto', sans-serif !important;
        }

        /* Estilos específicos */
        .badge-status {
            display: inline-block;
            padding: 0.4em 0.8em;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 500;
            min-width: 80px;
            text-align: center;
        }

        .search-box .input-group {
            width: 300px;
        }

        .search-box .form-control {
            border-radius: 20px 0 0 20px;
            border-right: none;
        }

        .search-box .input-group-text {
            border-radius: 0 20px 20px 0;
            border-left: none;
        }

        .fw-500 {
            font-weight: 500;
        }

        /* Eliminar espacios innecesarios */
        .content-wrapper {
            padding-bottom: 0 !important;
        }

        .wrapper {
            min-height: auto !important;
        }

        .card {
            margin-bottom: 0;
        }

        .table thead th {
            font-weight: 700 !important;
            font-size: 0.85rem;
        }

        .table tbody td {
            font-size: 0.875rem;
        }
    </style>
@stop
