@extends('adminlte::page')

@section('title', 'Usuarios')

@section('content_header')
    <h1>{{ __('Usuarios') }}</h1>
@stop

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
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
                    <form method="GET" action="{{ route('users.index') }}">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control border-end-0" placeholder="Buscar usuario..."
                                aria-label="Buscar" value="{{ $search ?? '' }}">
                            <button type="submit" class="input-group-text bg-white border-start-0">
                                <i class="fas fa-search text-secondary"></i>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Botón Agregar con espaciado corregido -->
                <a href="{{ route('users.create') }}" class="btn btn-primary rounded-3">
                    <i class="fas fa-plus me-2"></i>
                    <span>Agregar nuevo</span>
                </a>
            </div>
        </div>

        <!-- Cuerpo con tabla -->
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="fw-bold text-secondary text-uppercase border-light-subtle">{{ __('Usuario') }}</th>
                            <th class="fw-bold text-secondary text-uppercase border-light-subtle">{{ __('Email') }}</th>
                            <th class="fw-bold text-secondary text-uppercase border-light-subtle">{{ __('Estaciones de Trabajo') }}</th>
                            <th class="fw-bold text-secondary text-uppercase border-light-subtle">{{ __('Acciones') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr class="border-light-subtle">
                                <td class="py-3">
                                    <div class="d-flex align-items-center">
                                        <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}"
                                             class="rounded-circle me-3" width="40" height="40">
                                        <div>
                                            <div class="ml-2 fw-500">{{ $user->name }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3">
                                    <div class="fw-500">{{ $user->email }}</div>
                                </td>
                                <td class="py-3">
                                    @if($user->workCenters->count() > 0)
                                        <div class="d-flex flex-wrap gap-1">
                                            @foreach($user->workCenters as $workCenter)
                                                <span class="badge-status bg-primary bg-opacity-10 text-primary mr-2 mt-1">
                                                    {{ $workCenter->name }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="badge-status bg-secondary bg-opacity-10 text-secondary">
                                            Sin estaciones
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3">
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-outline-primary rounded-3">
                                            <i class="fas fa-edit me-2"></i>
                                            <span>Editar</span>
                                        </a>
                                        <form action="{{ route('users.destroy', $user) }}" method="POST" style="display:inline;" class="delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-3">
                                                <i class="fas fa-trash me-2"></i>
                                                <span>Eliminar</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="fas fa-users fa-2x text-muted mb-2"></i>
                                        <span class="text-secondary">No se encontraron usuarios</span>
                                        @if (!empty($search))
                                            <a href="{{ route('users.index') }}" class="btn btn-sm btn-link mt-2">
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
        @if ($users->hasPages() || $users->total() > 0)
            <div class="card-footer bg-white border-0 py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <!-- Información de resultados -->
                    <div class="text-muted small">
                        Mostrando {{ $users->firstItem() }} a {{ $users->lastItem() }} de
                        {{ $users->total() }} resultados
                    </div>

                    <!-- Controles de paginación -->
                    @if ($users->hasPages())
                        <nav aria-label="Page navigation">
                            <ul class="pagination mb-0">
                                {{-- Previous Page Link --}}
                                @if ($users->onFirstPage())
                                    <li class="page-item disabled">
                                        <span class="page-link" aria-hidden="true">&laquo;</span>
                                    </li>
                                @else
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $users->previousPageUrl() }}" rel="prev"
                                            aria-label="Previous">
                                            &laquo;
                                        </a>
                                    </li>
                                @endif

                                {{-- Pagination Elements --}}
                                @foreach ($users->getUrlRange(1, $users->lastPage()) as $page => $url)
                                    @if ($page == $users->currentPage())
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
                                @if ($users->hasMorePages())
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $users->nextPageUrl() }}" rel="next"
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
            padding: 0.4em 0.8em;
            text-align: center;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 500;
            min-width: 60px;
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

        /* Estilos para los botones de acción */
        .btn {
            display: inline-flex;
            align-items: center;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .btn i {
            margin-right: 0.5rem;
        }

        .btn-sm {
            padding: 0.35rem 0.75rem;
            font-size: 0.85rem;
        }

        .gap-2 {
            gap: 0.5rem;
        }

        /* Alertas */
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

        /* Imagen de perfil */
        .rounded-circle {
            object-fit: cover;
        }
    </style>
@stop

@section('js')
    <script>
        // Confirmación antes de eliminar
        document.addEventListener('DOMContentLoaded', function() {
            // Verificar si SweetAlert2 está disponible
            if (typeof Swal === 'undefined') {
                console.error('SweetAlert2 no está disponible. Asegúrate de que esté incluido en AdminLTE.');
                return;
            }

            // Agregar event listener a todos los formularios de eliminación
            document.querySelectorAll('.delete-form').forEach(form => {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();

                    Swal.fire({
                        title: '¿Estás seguro?',
                        text: "¡No podrás revertir esta acción!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Sí, eliminar',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            this.submit();
                        }
                    });
                });
            });

            // Cerrar alertas automáticamente después de 5 segundos
            setTimeout(() => {
                const alerts = document.querySelectorAll('.alert');
                alerts.forEach(alert => {
                    if (bootstrap && bootstrap.Alert) {
                        const bsAlert = new bootstrap.Alert(alert);
                        bsAlert.close();
                    }
                });
            }, 5000);
        });
    </script>
@stop
