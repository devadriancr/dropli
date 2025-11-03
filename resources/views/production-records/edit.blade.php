@extends('adminlte::page')

@section('title', 'Registro de Producción')

@section('content_header')
    <h1>{{ __('Registro de Producción') }}</h1>
@stop

@section('content')
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="card-body">
            <form action="{{ route('production-records.update', $productionRecord) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row mb-4">
                    <!-- Centro de Trabajo (solo lectura) -->
                    <div class="col-md-6 mb-3">
                        <label for="work_center" class="form-label fw-bold text-secondary">{{ __('Centro de Trabajo') }}</label>
                        <input type="text" id="work_center"
                               class="form-control border-light-subtle bg-light"
                               value="{{ $productionRecord->partNumber && $productionRecord->partNumber->workCenter ? $productionRecord->partNumber->workCenter->name : 'N/A' }}"
                               readonly
                               style="cursor: not-allowed;">
                    </div>

                    <!-- Número de Parte (solo lectura) -->
                    <div class="col-md-6 mb-3">
                        <label for="part_number" class="form-label fw-bold text-secondary">{{ __('Número de Parte') }}</label>
                        <input type="text" id="part_number"
                               class="form-control border-light-subtle bg-light"
                               value="{{ $productionRecord->partNumber ? $productionRecord->partNumber->number : 'N/A' }}"
                               readonly
                               style="cursor: not-allowed;">
                    </div>

                    <!-- Secuencia (solo lectura) -->
                    <div class="col-md-6 mb-3">
                        <label for="sequence" class="form-label fw-bold text-secondary">{{ __('Secuencia') }}</label>
                        <input type="text" id="sequence"
                               class="form-control border-light-subtle bg-light"
                               value="{{ $productionRecord->sequence ?? 'N/A' }}"
                               readonly
                               style="cursor: not-allowed;">
                    </div>

                    <!-- Tipo de Registro (solo lectura) -->
                    <div class="col-md-6 mb-3">
                        <label for="record_type" class="form-label fw-bold text-secondary">{{ __('Tipo de Registro') }}</label>
                        <input type="text" id="record_type"
                               class="form-control border-light-subtle bg-light"
                               value="{{ $productionRecord->record_type == 'entry' ? 'Entrada' : ($productionRecord->record_type == 'exit' ? 'Salida' : 'N/A') }}"
                               readonly
                               style="cursor: not-allowed;">
                    </div>

                    <!-- Fecha de Creación (solo lectura) -->
                    <div class="col-md-6 mb-3">
                        <label for="created_at" class="form-label fw-bold text-secondary">{{ __('Fecha de Creación') }}</label>
                        <input type="text" id="created_at"
                               class="form-control border-light-subtle bg-light"
                               value="{{ $productionRecord->created_at->format('Y-m-d H:i:s') }}"
                               readonly
                               style="cursor: not-allowed;">
                    </div>

                    <!-- Cantidad (EDITABLE) -->
                    <div class="col-md-6 mb-3">
                        <label for="quantity" class="form-label fw-bold text-secondary">{{ __('Cantidad') }} *</label>
                        <input type="number" name="quantity" id="quantity" min="1" step="1"
                               class="form-control border-light-subtle @error('quantity') is-invalid @enderror"
                               value="{{ old('quantity', $productionRecord->quantity) }}"
                               required
                               placeholder="Ingrese la cantidad"
                               autofocus>
                        @error('quantity')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('production-records.index') }}" class="btn btn-outline-secondary rounded-3">
                        <i class="fas fa-times me-2"></i> {{ __('Cancelar') }}
                    </a>
                    <button type="submit" class="btn btn-primary rounded-3">
                        <i class="fas fa-save me-2"></i> {{ __('Actualizar Cantidad') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
@stop

@section('css')
    <!-- Fuente Google Roboto -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">

    <style>
        body,
        .main-header,
        .main-sidebar,
        .content-wrapper,
        .card,
        .btn,
        .form-control,
        .form-select,
        .form-label,
        .table,
        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            font-family: 'Roboto', sans-serif !important;
        }

        .border-light-subtle {
            border-color: #f0f0f0 !important;
        }

        .form-control {
            border-radius: 8px !important;
            padding: 0.5rem 1rem !important;
            font-size: 0.95rem !important;
            border: 1px solid #e0e0e0 !important;
            transition: all 0.2s ease;
        }

        .form-control:focus {
            border-color: #86b7fe !important;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25) !important;
            outline: none !important;
        }

        .form-control:read-only {
            background-color: #f8f9fa !important;
            cursor: not-allowed !important;
        }

        .form-control.bg-light {
            background-color: #f8f9fa !important;
        }

        .form-label {
            font-size: 0.9rem !important;
            margin-bottom: 0.5rem !important;
            font-weight: 600 !important;
        }

        .btn {
            border-radius: 8px !important;
            padding: 0.5rem 1.5rem !important;
            font-weight: 500 !important;
            font-size: 0.95rem !important;
            display: inline-flex;
            align-items: center;
            transition: all 0.2s ease;
        }

        .btn i {
            font-size: 0.9rem !important;
            margin-right: 0.5rem !important;
        }

        .gap-2 {
            gap: 0.5rem;
        }

        .rounded-3 {
            border-radius: 12px !important;
        }

        .invalid-feedback {
            font-size: 0.85rem !important;
        }

        .form-text {
            font-size: 0.8rem !important;
        }
    </style>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            // Foco automático al campo de cantidad
            $('#quantity').focus();

            // Validación adicional
            $('#quantity').on('input', function() {
                if (this.value < 1) {
                    this.setCustomValidity('La cantidad debe ser al menos 1');
                } else {
                    this.setCustomValidity('');
                }
            });
        });
    </script>
@stop
