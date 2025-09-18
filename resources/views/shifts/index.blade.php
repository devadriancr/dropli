@extends('adminlte::page')

@section('title', 'Turnos')

@section('content_header')
    <h1>{{ __('Turnos') }}</h1>
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
        <!-- Header con buscador y botón -->
        <div class="card-header bg-white border-0 py-3">
            <div class="d-flex justify-content-between align-items-center">
                <!-- Buscador -->
                <div class="search-box">
                    <form method="GET" action="{{ route('shifts.index') }}">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control border-end-0"
                                   placeholder="Buscar por abreviatura, nombre..."
                                   aria-label="Buscar" value="{{ $search ?? '' }}">
                            <button type="submit" class="input-group-text bg-white border-start-0">
                                <i class="fas fa-search text-secondary"></i>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Botón Agregar -->
                <a href="{{ route('shifts.create') }}" class="btn btn-primary rounded-3">
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
                            <th class="fw-bold text-secondary text-uppercase">{{ __('Abreviatura') }}</th>
                            <th class="fw-bold text-secondary text-uppercase">{{ __('Nombre') }}</th>
                            <th class="fw-bold text-secondary text-uppercase">{{ __('Horario') }}</th>
                            <th class="fw-bold text-secondary text-uppercase">{{ __('Descripción') }}</th>
                            <th class="fw-bold text-secondary text-uppercase">{{ __('Acciones') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($shifts as $shift)
                            <tr>
                                <td class="py-3">
                                    <span class="badge-status bg-primary bg-opacity-10 text-primary">
                                        {{ $shift->abbreviation }}
                                    </span>
                                </td>
                                <td class="py-3 small">{{ $shift->name }}</td>
                                <td class="py-3 small">
                                    {{ $shift->start_time }} - {{ $shift->end_time }}
                                </td>
                                <td class="py-3 small">{{ $shift->description ?? '-' }}</td>
                                <td class="py-3">
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('shifts.edit', $shift) }}"
                                           class="btn btn-sm btn-outline-primary rounded-3">
                                            <i class="fas fa-edit me-2"></i>
                                            <span>Editar</span>
                                        </a>
                                        <form action="{{ route('shifts.destroy', $shift) }}" method="POST"
                                              style="display:inline;" class="delete-form">
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
                                <td colspan="5" class="text-center py-4">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="fas fa-clock fa-2x text-muted mb-2"></i>
                                        <span class="text-secondary">No se encontraron turnos</span>
                                        @if(!empty($search))
                                            <a href="{{ route('shifts.index') }}" class="btn btn-sm btn-link mt-2">
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
        @if ($shifts->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted small">
                        Mostrando {{ $shifts->firstItem() }} a {{ $shifts->lastItem() }} de
                        {{ $shifts->total() }} resultados
                    </div>

                    {{ $shifts->links('pagination::bootstrap-4') }}
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
        .pagination,
        .alert {
            font-family: 'Roboto', sans-serif !important;
        }

        /* Estilos específicos */
        .badge-status {
            display: inline-block;
            padding: 0.4em 0.8em;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 500;
            min-width: 60px;
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

        .btn {
            display: inline-flex;
            align-items: center;
            font-weight: 500;
        }

        .btn i {
            margin-right: 0.5rem;
        }

        .gap-2 {
            gap: 0.5rem;
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

        /* Alertas */
        .alert {
            border-radius: 8px;
        }
    </style>
@stop

@section('js')
    <script>
        // Confirmación antes de eliminar
        document.addEventListener('DOMContentLoaded', function() {
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
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                });
            }, 5000);
        });
    </script>
@stop
