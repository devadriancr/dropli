@extends('adminlte::page')

@section('title', 'Paquetes Estándar')

@section('content_header')
    <h1>{{ __('Paquetes Estándar') }}</h1>
@stop

@section('content')
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <!-- Header con buscador -->
        <div class="card-header bg-white border-0 py-3">
            <div class="d-flex justify-content-between align-items-center">
                <!-- Buscador -->
                <div class="search-box">
                    <form method="GET" action="{{ route('standard-packs.index') }}">
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
                            <th class="fw-bold text-secondary text-uppercase border-light-subtle">{{ __('Nombre') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($standardPacks as $standardPack)
                            <tr class="border-light-subtle">
                                <td class="py-3 small">{{ $standardPack->name }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="1" class="text-center py-4">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                                        <span class="text-secondary">No se encontraron resultados</span>
                                        @if (!empty($search))
                                            <a href="{{ route('standard-packs.index') }}" class="btn btn-sm btn-link mt-2">
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
        @if ($standardPacks->hasPages() || $standardPacks->total() > 0)
            <div class="card-footer bg-white border-0 py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <!-- Información de resultados -->
                    <div class="text-muted small">
                        Mostrando {{ $standardPacks->firstItem() }} a {{ $standardPacks->lastItem() }} de
                        {{ $standardPacks->total() }} resultados
                    </div>

                    <!-- Controles de paginación -->
                    @if ($standardPacks->hasPages())
                        <nav aria-label="Page navigation">
                            <ul class="pagination mb-0">
                                {{-- Previous Page Link --}}
                                @if ($standardPacks->onFirstPage())
                                    <li class="page-item disabled">
                                        <span class="page-link" aria-hidden="true">&laquo;</span>
                                    </li>
                                @else
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $standardPacks->previousPageUrl() }}" rel="prev"
                                            aria-label="Previous">
                                            &laquo;
                                        </a>
                                    </li>
                                @endif

                                {{-- Pagination Elements --}}
                                @foreach ($standardPacks->getUrlRange(1, $standardPacks->lastPage()) as $page => $url)
                                    @if ($page == $standardPacks->currentPage())
                                        <li class="page-item active" aria-current="page">
                                            <span class="page-link">{{ $page }}</span>
                                        </li>
                                    @else
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                                        </li>
                                    @endif
                                @endforeach

                                {{-- Next Page Link --}}
                                @if ($standardPacks->hasMorePages())
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $standardPacks->nextPageUrl() }}" rel="next"
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

        /* Botones de acción */
        .action-btn {
            display: inline-flex;
            align-items: center;
            font-size: 0.9rem;
            font-weight: 500;
            text-decoration: none !important;
            transition: all 0.2s ease;
            padding: 0.4rem 0.8rem;
            border-radius: 6px;
        }

        .action-btn i {
            font-size: 0.9rem;
            transition: transform 0.2s ease;
        }

        .action-btn:hover {
            background-color: rgba(0, 0, 0, 0.03);
        }

        .action-btn:hover i {
            transform: scale(1.1);
        }

        .action-btn.text-primary:hover {
            color: #0d62c9 !important;
        }

        .action-btn.text-danger:hover {
            color: #c21807 !important;
        }

        /* Botón Agregar sin animación */
        .add-btn {
            display: inline-flex;
            align-items: center;
            font-size: 0.95rem;
            font-weight: 500;
            text-decoration: none !important;
            transition: all 0.2s ease;
            padding: 0.6rem 1.2rem;
            border-radius: 8px;
            color: #1a73e8 !important;
        }

        .add-btn:hover {
            background-color: rgba(26, 115, 232, 0.08);
            color: #0d62c9 !important;
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

        /* Ajustes de espaciado para paginación */
        .card-footer .pagination {
            margin-bottom: 0;
        }
    </style>
@stop

@section('js')
    <script>
        // Efecto hover suave para las filas
        // document.querySelectorAll('.table-hover tbody tr').forEach(row => {
        //     row.addEventListener('mouseenter', function() {
        //         this.style.transition = 'all 0.2s ease';
        //         this.style.boxShadow = '0 2px 8px rgba(0,0,0,0.05)';
        //         this.style.transform = 'translateY(-1px)';
        //     });

        //     row.addEventListener('mouseleave', function() {
        //         this.style.boxShadow = 'none';
        //         this.style.transform = 'translateY(0)';
        //     });
        // });

        // Foco automático al buscador
        // document.addEventListener('DOMContentLoaded', function() {
        //     const searchInput = document.querySelector('.search-box .form-control');
        //     if (searchInput && searchInput.value === '') {
        //         searchInput.focus();
        //     }
        // });
    </script>
@stop
