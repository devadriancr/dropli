@extends('adminlte::page')

@section('title', 'Tipo de Clase')

@section('content_header')
    <h1>{{ __('Tipo de Clase') }}</h1>
@stop

@section('content')
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <!-- Header con buscador -->
        <div class="card-header bg-white border-0 py-3">
            <div class="search-box">
                <form method="GET" action="{{ route('item-classes.index') }}">
                    <div class="input-group">
                        <input
                            type="text"
                            name="search"
                            class="form-control border-end-0"
                            placeholder="Buscar..."
                            aria-label="Buscar"
                            value="{{ $search ?? '' }}"
                        >
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
                            <th class="fw-bold text-secondary text-uppercase">{{ __('Abreviatura') }}</th>
                            <th class="fw-bold text-secondary text-uppercase">{{ __('Nombre') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($itemClasses as $itemClass)
                            <tr>
                                <td class="py-3">
                                    <span class="badge-status bg-primary bg-opacity-10 text-primary">{{ $itemClass->abbreviation }}</span>
                                </td>
                                <td class="py-3 small">{{ $itemClass->name }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="text-center py-4">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                                        <span class="text-secondary">No se encontraron resultados</span>
                                        @if(!empty($search))
                                            <a href="{{ route('item-classes.index') }}"
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
        @if ($itemClasses->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted small">
                        Mostrando {{ $itemClasses->firstItem() }} a {{ $itemClasses->lastItem() }} de
                        {{ $itemClasses->total() }} resultados
                    </div>

                    {{ $itemClasses->links('pagination::bootstrap-4') }}
                </div>
            </div>
        @endif
    </div>
@stop

@section('css')
    <!-- Fuente Google Roboto -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.comcss2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">

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
            min-width: 90px;
            text-align: center;
        }

        .search-box .input-group {
            width: 380px;
        }

        .search-box .form-control {
            border-radius: 20px 0 0 20px;
            border-right: none;
        }

        .search-box .input-group-text {
            border-radius: 0 20px 20px 0;
            border-left: none;
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

        /* Mejorar legibilidad de la tabla */
        .table thead th {
            font-weight: 700 !important;
            font-size: 0.85rem;
        }

        .table tbody td {
            font-size: 0.875rem;
        }

        /* Efecto hover para filas de tabla */
        .table-hover tbody tr:hover {
            background-color: #f8f9fa;
        }
    </style>
@stop
