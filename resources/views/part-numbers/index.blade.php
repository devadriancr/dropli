@extends('adminlte::page')

@section('title', 'Números de Parte')

@section('content_header')
    <h1>{{ __('Números de Parte') }}</h1>
@stop

@section('content')
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <!-- Header con buscador -->
        <div class="card-header bg-white border-0 py-3">
            <div class="d-flex justify-content-between align-items-center">
                <!-- Buscador -->
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
        </div>

        <!-- Cuerpo con tabla -->
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="fw-bold text-secondary text-uppercase border-light-subtle">{{ __('Departamento') }}
                            </th>
                            <th class="fw-bold text-secondary text-uppercase border-light-subtle">{{ __('Área') }}</th>
                            <th class="fw-bold text-secondary text-uppercase border-light-subtle">
                                {{ __('Centro de Trabajo') }}</th>
                            <th class="fw-bold text-secondary text-uppercase border-light-subtle">
                                {{ __('Número de Parte') }}</th>
                            <th class="fw-bold text-secondary text-uppercase border-light-subtle">{{ __('Clase') }}</th>
                            <th class="fw-bold text-secondary text-uppercase border-light-subtle">
                                {{ __('Empaque Estándar') }}</th>
                            <th class="fw-bold text-secondary text-uppercase border-light-subtle">{{ __('Cantidad') }}</th>
                            <th class="fw-bold text-secondary text-uppercase border-light-subtle">{{ __('Estado') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($partNumbers as $partNumber)
                            <tr class="border-light-subtle">
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

                                <!-- Obsoleto -->
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
                                <td colspan="7" class="text-center py-4">
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

        <!-- Pie de página con paginación mejorada -->
        @if ($partNumbers->hasPages() || $partNumbers->total() > 0)
            <div class="card-footer bg-white border-0 py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <!-- Información de resultados -->
                    <div class="text-muted small">
                        Mostrando {{ $partNumbers->firstItem() }} a {{ $partNumbers->lastItem() }} de
                        {{ $partNumbers->total() }} resultados
                    </div>

                    <!-- Controles de paginación mejorados -->
                    @if ($partNumbers->hasPages())
                        <nav aria-label="Page navigation">
                            <ul class="pagination mb-0">
                                {{-- Previous Page Link --}}
                                @if ($partNumbers->onFirstPage())
                                    <li class="page-item disabled">
                                        <span class="page-link" aria-hidden="true">&laquo;</span>
                                    </li>
                                @else
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $partNumbers->previousPageUrl() }}" rel="prev"
                                            aria-label="Previous">
                                            &laquo;
                                        </a>
                                    </li>
                                @endif

                                {{-- Pagination Elements --}}
                                @php
                                    $current = $partNumbers->currentPage();
                                    $last = $partNumbers->lastPage();
                                    $start = max($current - 5, 1);
                                    $end = min($current + 5, $last);

                                    // Ajustar si estamos cerca del inicio o final
                                    if ($current <= 5) {
                                        $end = min(11, $last);
                                    }
                                    if ($current >= $last - 5) {
                                        $start = max($last - 10, 1);
                                    }
                                @endphp

                                {{-- Mostrar primera página si no está en el rango --}}
                                @if ($start > 1)
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $partNumbers->url(1) }}">1</a>
                                    </li>
                                    @if ($start > 2)
                                        <li class="page-item disabled">
                                            <span class="page-link">...</span>
                                        </li>
                                    @endif
                                @endif

                                {{-- Rango de páginas --}}
                                @for ($page = $start; $page <= $end; $page++)
                                    @if ($page == $partNumbers->currentPage())
                                        <li class="page-item active" aria-current="page">
                                            <span class="page-link">{{ $page }}</span>
                                        </li>
                                    @else
                                        <li class="page-item">
                                            <a class="page-link"
                                                href="{{ $partNumbers->url($page) }}">{{ $page }}</a>
                                        </li>
                                    @endif
                                @endfor

                                {{-- Mostrar última página si no está en el rango --}}
                                @if ($end < $last)
                                    @if ($end < $last - 1)
                                        <li class="page-item disabled">
                                            <span class="page-link">...</span>
                                        </li>
                                    @endif
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $partNumbers->url($last) }}">{{ $last }}</a>
                                    </li>
                                @endif

                                {{-- Next Page Link --}}
                                @if ($partNumbers->hasMorePages())
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $partNumbers->nextPageUrl() }}" rel="next"
                                            aria-label="Next">
                                            &raquo;
                                        </a>
                                    </li>
                                @else
                                    <li class="page-item disabled">
                                        <span class="page-link" aria-hidden="true">&raquo;</span>
                                    </li>
                                @endif
                            </ul>
                        </nav>
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
        /* Aplicar fuente a todo el sistema */
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
        h6 {
            font-family: 'Roboto', sans-serif !important;
        }

        /* Estilos adicionales para la tabla */
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

        /* Mejoras en jerarquía tipográfica */
        .table thead th {
            font-weight: 700 !important;
            font-size: 0.85rem;
        }

        .table tbody td {
            font-size: 0.875rem;
        }

        /* Buscador sin contorno azul */
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

        /* Quitar contorno azul al enfocar */
        .search-box .form-control:focus {
            border-color: #dee2e6 !important;
            box-shadow: none !important;
            outline: none !important;
        }

        /* Badges simétricos */
        .badge-status {
            display: inline-block;
            min-width: 90px;
            padding: 0.5em 0.75em;
            text-align: center;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        /* Estilos para la paginación */
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

        /* Estilos para el contador de resultados */
        .text-muted.small {
            font-size: 0.85rem;
            color: #6c757d;
        }

        /* Estilo para texto resaltado */
        .fw-500 {
            font-weight: 500 !important;
        }
    </style>
@stop
