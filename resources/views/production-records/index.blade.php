@extends('adminlte::page')

@section('title', 'Registros de Producción')

@section('content_header')
    <h1 class="fw-bold">{{ __('Registros de Producción') }}</h1>
@stop

@section('content')
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <!-- Header con buscador -->
        <div class="card-header bg-white border-0 py-3">
            <div class="d-flex justify-content-between align-items-center">
                <div class="search-box">
                    <form method="GET" action="{{ route('production-records.index') }}">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control border-end-0"
                                   placeholder="Buscar..." value="{{ $search ?? '' }}">
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
                            <th class="fw-bold text-secondary text-uppercase border-light-subtle">{{ __('Centro de Trabajo') }}</th>
                            <th class="fw-bold text-secondary text-uppercase border-light-subtle">{{ __('Número de Parte') }}</th>
                            <th class="fw-bold text-secondary text-uppercase border-light-subtle">{{ __('Secuencia') }}</th>
                            <th class="fw-bold text-secondary text-uppercase border-light-subtle">{{ __('Cantidad') }}</th>
                            <th class="fw-bold text-secondary text-uppercase border-light-subtle">{{ __('Fecha de Creación') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($productionRecords as $record)
                            <tr class="border-light-subtle">
                                <!-- Estación/Centro de Trabajo -->
                                <td class="py-3 small">
                                    @if ($record->partNumber && $record->partNumber->workCenter)
                                        <div class="fw-500">{{ $record->partNumber->workCenter->number }}</div>
                                        <div class="text-muted">{{ $record->partNumber->workCenter->name }}</div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>

                                <!-- Número de Parte -->
                                <td class="py-3 small">
                                    @if ($record->partNumber)
                                        <div class="fw-500">{{ $record->partNumber->number }}</div>
                                        <div class="text-muted">{{ $record->partNumber->name }}</div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>

                                <!-- Secuencia -->
                                <td class="py-3 small">
                                    @if(isset($record->sequence))
                                        <span class="badge-status bg-primary bg-opacity-10 text-primary">
                                            {{ $record->sequence }}
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>

                                <!-- Cantidad -->
                                <td class="py-3 small fw-500">
                                    {{ $record->quantity ?? '-' }}
                                </td>

                                <!-- Fecha de Creación -->
                                <td class="py-3 small">
                                    <div class="fw-500">{{ $record->created_at->format('d/m/Y') }}</div>
                                    <div class="text-muted">{{ $record->created_at->format('H:i:s') }}</div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="fas fa-clipboard-list fa-2x text-muted mb-2"></i>
                                        <span class="text-secondary">No se encontraron registros de producción</span>
                                        @if (!empty($search))
                                            <a href="{{ route('production-records.index') }}" class="btn btn-sm btn-link mt-2">
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
        @if ($productionRecords->hasPages() || $productionRecords->total() > 0)
            <div class="card-footer bg-white border-0 py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted small">
                        Mostrando {{ $productionRecords->firstItem() ?? 0 }} a {{ $productionRecords->lastItem() ?? 0 }}
                        de {{ $productionRecords->total() }} resultados
                    </div>
                    <div>
                        {{ $productionRecords->links('pagination::bootstrap-4') }}
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
