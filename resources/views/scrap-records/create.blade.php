@extends('adminlte::page')

@section('title', 'Crear Registro de Scrap')

@section('content_header')
    <h1>{{ __('Crear Registro de Scrap') }}</h1>
@stop

@section('content')
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="card-body">
            <form action="{{ route('scrap-records.store') }}" method="POST">
                @csrf

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="part_number_id" class="form-label fw-bold text-secondary">{{ __('Part Number') }} *</label>
                        <select name="part_number_id" id="part_number_id" class="form-control select2 border-light-subtle @error('part_number_id') is-invalid @enderror" required>
                            <option value="">{{ __('Seleccione un part number') }}</option>
                            @foreach($partNumbers as $partNumber)
                                <option value="{{ $partNumber->id }}" {{ old('part_number_id') == $partNumber->id ? 'selected' : '' }}>
                                    {{ $partNumber->number }} - {{ $partNumber->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('part_number_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="scrap_reason_id" class="form-label fw-bold text-secondary">{{ __('Razón de Scrap') }} *</label>
                        <select name="scrap_reason_id" id="scrap_reason_id" class="form-control select2 border-light-subtle @error('scrap_reason_id') is-invalid @enderror" required>
                            <option value="">{{ __('Seleccione una razón') }}</option>
                            @foreach($scrapReasons as $reason)
                                <option value="{{ $reason->id }}" {{ old('scrap_reason_id') == $reason->id ? 'selected' : '' }}>
                                    {{ $reason->code }} - {{ $reason->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('scrap_reason_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <label for="quantity" class="form-label fw-bold text-secondary">{{ __('Cantidad') }} *</label>
                        <input type="number" name="quantity" id="quantity" min="1" step="1"
                               class="form-control border-light-subtle @error('quantity') is-invalid @enderror"
                               value="{{ old('quantity') }}" required
                               placeholder="Ingrese la cantidad">
                        @error('quantity')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('scrap-records.index') }}" class="btn btn-outline-secondary rounded-3">
                        <i class="fas fa-times me-2"></i> {{ __('Cancelar') }}
                    </a>
                    <button type="submit" class="btn btn-primary rounded-3">
                        <i class="fas fa-save me-2"></i> {{ __('Guardar') }}
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
        /* Aplicar fuente a todo el sistema */
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

        /* Estilos para formularios */
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

        .form-label {
            font-size: 0.9rem !important;
            margin-bottom: 0.5rem !important;
            font-weight: 600 !important;
        }

        /* Estilos para Select2 */
        .select2-container--default .select2-selection--single {
            height: 38px !important;
            border: 1px solid #e0e0e0 !important;
            border-radius: 8px !important;
            padding: 0 !important;
            font-family: 'Roboto', sans-serif !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 36px !important;
            padding-left: 12px !important;
            padding-right: 20px !important;
            font-size: 0.95rem !important;
            color: #495057 !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__placeholder {
            color: #6c757d !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px !important;
            right: 8px !important;
        }

        .select2-container--default.select2-container--focus .select2-selection--single {
            border-color: #86b7fe !important;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25) !important;
        }

        .select2-dropdown {
            border: 1px solid #e0e0e0 !important;
            border-radius: 8px !important;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1) !important;
        }

        .select2-results__option {
            font-family: 'Roboto', sans-serif !important;
            font-size: 0.95rem !important;
            padding: 8px 12px !important;
        }

        .select2-results__option--highlighted {
            background-color: #007bff !important;
        }

        /* Botones */
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

        /* Card */
        .rounded-3 {
            border-radius: 12px !important;
        }

        /* Invalid feedback */
        .invalid-feedback {
            font-size: 0.85rem !important;
        }

        /* Placeholder styling */
        ::placeholder {
            color: #6c757d !important;
            opacity: 0.7;
        }
    </style>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            // Inicializar Select2
            $('#part_number_id, #scrap_reason_id').select2({
                placeholder: 'Seleccione una opción',
                allowClear: false,
                width: '100%'
            });

            // Foco automático al primer campo
            setTimeout(function() {
                $('#part_number_id').select2('focus');
            }, 100);

            // Manejar errores de validación para Select2
            @if($errors->has('part_number_id'))
                $('#part_number_id').next('.select2-container').find('.select2-selection').addClass('is-invalid');
            @endif

            @if($errors->has('scrap_reason_id'))
                $('#scrap_reason_id').next('.select2-container').find('.select2-selection').addClass('is-invalid');
            @endif
        });
    </script>
@stop
