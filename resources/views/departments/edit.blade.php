@extends('adminlte::page')

@section('title', 'Editar Departamento')

@section('content_header')
    <h1>{{ __('Editar Departamento') }}</h1>
@stop

@section('content')
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="card-body">
            <form action="{{ route('departments.update', $department->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="code" class="form-label fw-bold text-secondary">{{ __('Código') }}</label>
                        <input type="text" name="code" id="code" class="form-control border-light-subtle @error('code') is-invalid @enderror"
                               value="{{ old('code', $department->code) }}" required maxlength="20" placeholder="Ej: DEPT01">
                        @error('code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Código único para identificar el departamento</div>
                    </div>

                    <div class="col-md-6">
                        <label for="name" class="form-label fw-bold text-secondary">{{ __('Nombre del Departamento') }}</label>
                        <input type="text" name="name" id="name" class="form-control border-light-subtle @error('name') is-invalid @enderror"
                               value="{{ old('name', $department->name) }}" required maxlength="255" placeholder="Ej: Recursos Humanos">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-4">
                    <label for="description" class="form-label fw-bold text-secondary">{{ __('Descripción') }}</label>
                    <textarea name="description" id="description" class="form-control border-light-subtle @error('description') is-invalid @enderror"
                              rows="3" maxlength="500" placeholder="Describe las funciones y responsabilidades del departamento...">{{ old('description', $department->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">Máximo 500 caracteres</div>
                </div>

                <!-- Información adicional -->
                @if($department->lines()->count() > 0)
                    <div class="alert alert-info mb-4">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Información:</strong> Este departamento tiene {{ $department->lines()->count() }} área(s) asociada(s).
                    </div>
                @endif

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('departments.index') }}" class="btn btn-outline-secondary rounded-3">
                        <i class="fas fa-times me-2"></i> {{ __('Cancelar') }}
                    </a>
                    <button type="submit" class="btn btn-primary rounded-3">
                        <i class="fas fa-save me-2"></i> {{ __('Actualizar') }}
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

        .form-text {
            font-size: 0.8rem !important;
            color: #6c757d !important;
            margin-top: 0.25rem !important;
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

        /* Alert styling */
        .alert {
            border-radius: 8px !important;
            border: none !important;
        }

        .alert-info {
            background-color: rgba(13, 202, 240, 0.1) !important;
            color: #055160 !important;
        }
    </style>
@stop

@section('js')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Foco automático al primer campo
            const firstInput = document.querySelector('#code');
            if (firstInput) {
                firstInput.focus();
            }

            // Convertir código a mayúsculas automáticamente
            const codeInput = document.querySelector('#code');
            if (codeInput) {
                codeInput.addEventListener('input', function() {
                    this.value = this.value.toUpperCase();
                });
            }

            // Contador de caracteres para descripción
            const descriptionTextarea = document.querySelector('#description');
            if (descriptionTextarea) {
                const maxLength = descriptionTextarea.getAttribute('maxlength');
                const formText = descriptionTextarea.parentNode.querySelector('.form-text');

                // Mostrar contador inicial
                const currentLength = descriptionTextarea.value.length;
                formText.textContent = `${currentLength}/${maxLength} caracteres`;

                descriptionTextarea.addEventListener('input', function() {
                    const currentLength = this.value.length;
                    formText.textContent = `${currentLength}/${maxLength} caracteres`;

                    if (currentLength > maxLength * 0.9) {
                        formText.style.color = '#dc3545';
                    } else {
                        formText.style.color = '#6c757d';
                    }
                });
            }
        });
    </script>
@stop
