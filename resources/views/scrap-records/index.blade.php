@extends('adminlte::page')

@section('title', 'Registros de Scrap')

@section('content_header')
    <h1 class="fw-bold">{{ __('Registros de Scrap') }}</h1>
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
                    <form method="GET" action="{{ route('scrap-records.index') }}">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control border-end-0"
                                   placeholder="Buscar..." value="{{ $search ?? '' }}">
                            <button type="submit" class="input-group-text bg-white border-start-0">
                                <i class="fas fa-search text-secondary"></i>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Botón Agregar -->
                <a href="{{ route('scrap-records.create') }}" class="btn btn-primary rounded-3">
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
                            <th class="fw-bold text-secondary text-uppercase border-light-subtle">{{ __('Part Number') }}</th>
                            <th class="fw-bold text-secondary text-uppercase border-light-subtle">{{ __('Razón de Scrap') }}</th>
                            <th class="fw-bold text-secondary text-uppercase border-light-subtle">{{ __('Cantidad') }}</th>
                            <th class="fw-bold text-secondary text-uppercase border-light-subtle">{{ __('Fecha') }}</th>
                            <th class="fw-bold text-secondary text-uppercase border-light-subtle">{{ __('Acciones') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($scrapRecords as $record)
                            <tr class="border-light-subtle">
                                <td class="py-3 small">
                                    @if($record->partNumber)
                                        <div class="fw-500">{{ $record->partNumber->number }}</div>
                                        <div class="text-muted">{{ $record->partNumber->name ?? '-' }}</div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="py-3 small">
                                    @if($record->scrapReason)
                                        <div class="fw-500">{{ $record->scrapReason->code }}</div>
                                        <div class="text-muted">{{ $record->scrapReason->name }}</div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="py-3 small">
                                    <span class="badge-status bg-primary bg-opacity-10 text-primary">
                                        {{ $record->quantity }}
                                    </span>
                                </td>
                                <td class="py-3 small">
                                    <div class="fw-500">{{ $record->created_at->format('d/m/Y') }}</div>
                                    <div class="text-muted">{{ $record->created_at->format('H:i:s') }}</div>
                                </td>
                                <td class="py-3">
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('scrap-records.edit', $record) }}"
                                           class="btn btn-sm btn-outline-primary" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('scrap-records.destroy', $record) }}"
                                              method="POST" style="display:inline;" class="delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                                        <span class="text-secondary">No se encontraron resultados</span>
                                        @if (!empty($search))
                                            <a href="{{ route('scrap-records.index') }}" class="btn btn-sm btn-link mt-2">
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

        <!-- Footer -->
        @if ($scrapRecords->hasPages() || $scrapRecords->total() > 0)
            <div class="card-footer bg-white border-0 py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted small">
                        Mostrando {{ $scrapRecords->firstItem() ?? 0 }} a {{ $scrapRecords->lastItem() ?? 0 }}
                        de {{ $scrapRecords->total() }} resultados
                    </div>
                    <div>
                        {{ $scrapRecords->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            </div>
        @endif
    </div>
@stop

@section('css')
    <style>
        body { font-family: 'Roboto', sans-serif !important; }
        .border-light-subtle { border-color: #f0f0f0 !important; }
        .table-hover tbody tr:hover {
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            transform: translateY(-1px);
            transition: all 0.2s ease;
        }
        .rounded-3 { border-radius: 12px !important; }
        .fw-500 { font-weight: 500 !important; }

        /* Buscador */
        .search-box .input-group { width: 320px; }
        .search-box .form-control {
            border-radius: 20px 0 0 20px !important;
            border-right: none;
            padding: 0.5rem 1rem;
            height: 42px;
        }
        .search-box .input-group-text {
            border-radius: 0 20px 20px 0 !important;
            border-left: none;
            background-color: white;
            padding: 0 1rem;
        }
        .search-box .form-control:focus {
            border-color: #dee2e6 !important;
            box-shadow: none !important;
        }

        /* Badges */
        .badge-status {
            display: inline-block;
            min-width: 90px;
            padding: 0.4em 0.75em;
            text-align: center;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        /* Paginación */
        .pagination { margin-bottom: 0; }
        .page-item .page-link {
            border-radius: 8px;
            margin: 0 3px;
            border: none;
            color: #6c757d;
            font-size: 0.9rem;
            min-width: 32px;
            text-align: center;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
            padding: 6px 12px;
        }
        .page-item.active .page-link { background-color: #1a73e8; color: white; }
        .page-item:not(.active) .page-link:hover {
            background-color: #f8f9fa;
            color: #1a73e8;
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
