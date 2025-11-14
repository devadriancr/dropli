@extends('adminlte::page')

@section('title', 'Crear Registro de Paro')

@section('content_header')
    <h1>{{ __('Crear Registro de Paro') }}</h1>
@stop

@section('content')
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="card-body">
            <form action="{{ route('downtime-records.store') }}" method="POST" id="downtimeForm">
                @csrf

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="work_center_id" class="form-label fw-bold text-secondary">{{ __('Centro de Trabajo') }} * </label>
                        <select name="work_center_id" id="work_center_id" class="form-control select2 border-light-subtle @error('work_center_id') is-invalid @enderror" required>
                            <option value="">{{ __('Seleccione un centro de trabajo') }}</option>
                            @foreach ($workCenters as $workCenter)
                                <option value="{{ $workCenter->id }}"
                                    {{ old('work_center_id') == $workCenter->id ? 'selected' : '' }}>
                                    {{ $workCenter->number }} - {{ $workCenter->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('work_center_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="downtime_reason_id" class="form-label fw-bold text-secondary">{{ __('Razón de Paro') }} * </label>
                        <select name="downtime_reason_id" id="downtime_reason_id" class="form-control select2 border-light-subtle @error('downtime_reason_id') is-invalid @enderror" required>
                            <option value="">{{ __('Seleccione una razón') }}</option>
                            @foreach ($downtimeReasons as $reason)
                                <option value="{{ $reason->id }}" {{ old('downtime_reason_id') == $reason->id ? 'selected' : '' }} data-type="{{ $reason->downtimeType->name ?? '' }}">
                                    {{ $reason->name }}
                                    @if ($reason->downtimeType)
                                        ({{ $reason->downtimeType->name }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        @error('downtime_reason_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="start_time" class="form-label fw-bold text-secondary">{{ __('Hora de Inicio') }} * </label>
                        <input type="datetime-local" name="start_time" id="start_time"
                            class="form-control border-light-subtle @error('start_time') is-invalid @enderror"
                            value="{{ old('start_time') }}" required>
                        @error('start_time')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="end_time" class="form-label fw-bold text-secondary">{{ __('Hora de Fin') }} *</label>
                        <input type="datetime-local" name="end_time" id="end_time"
                            class="form-control border-light-subtle @error('end_time') is-invalid @enderror"
                            value="{{ old('end_time') }}" required>
                        @error('end_time')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="minutes" class="form-label fw-bold text-secondary">{{ __('Minutos') }} *</label>
                        <input type="number" name="minutes" id="minutes" step="0.01" min="0"
                            class="form-control border-light-subtle @error('minutes') is-invalid @enderror"
                            value="{{ old('minutes') }}" required readonly>
                        @error('minutes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Calculado automáticamente</small>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('downtime-records.index') }}" class="btn btn-outline-secondary rounded-3">
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

        /* Botones - ESPACIADO CORREGIDO */
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

        /* Textarea */
        textarea.form-control {
            resize: vertical;
            min-height: 90px;
        }

        /* Placeholder styling */
        ::placeholder {
            color: #6c757d !important;
            opacity: 0.7;
        }

        /* Estilos para campos de fecha/hora */
        input[type="datetime-local"] {
            padding: 0.5rem 1rem !important;
        }
    </style>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            // Inicializar Select2
            $('#work_center_id, #downtime_reason_id').select2({
                placeholder: 'Seleccione una opción',
                allowClear: false,
                width: '100%'
            });

            // Función para calcular minutos
            function calculateMinutes() {
                const startTime = $('#start_time').val();
                const endTime = $('#end_time').val();

                if (startTime && endTime) {
                    const start = new Date(startTime);
                    const end = new Date(endTime);

                    if (end > start) {
                        const diffMs = end - start;
                        const minutes = Math.floor(diffMs / 60000); // 60000 ms = 1 minuto
                        $('#minutes').val(minutes);
                    } else {
                        $('#minutes').val(0);
                    }
                }
            }

            // Calcular minutos cuando cambien las fechas
            $('#start_time, #end_time').change(calculateMinutes);

            // Foco automático al primer campo
            setTimeout(function() {
                $('#work_center_id').select2('focus');
            }, 100);

            // Manejar errores de validación para Select2
            @if ($errors->has('work_center_id'))
                $('#work_center_id').next('.select2-container').find('.select2-selection').addClass('is-invalid');
            @endif

            @if ($errors->has('downtime_reason_id'))
                $('#downtime_reason_id').next('.select2-container').find('.select2-selection').addClass(
                    'is-invalid');
            @endif

            // Calcular minutos inicial si hay valores
            if ($('#start_time').val() && $('#end_time').val()) {
                calculateMinutes();
            }
        });
    </script>
@stop
