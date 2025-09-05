@extends('adminlte::page')

@section('title', 'Editar Categoría de Scrap')

@section('content_header')
    <h1>{{ __('Editar Categoría de Scrap') }}</h1>
@stop

@section('content')
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="card-body">
            <form action="{{ route('scrap-categories.update', $scrapCategory) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row mb-4">
                    <div class="col-md-6">
                        <label for="name" class="form-label fw-bold text-secondary">{{ __('Nombre') }} *</label>
                        <input type="text" name="name" id="name" class="form-control border-light-subtle @error('name') is-invalid @enderror"
                               value="{{ old('name', $scrapCategory->name) }}" required maxlength="255"
                               placeholder="Ingrese el nombre de la categoría">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-4">
                    <label for="description" class="form-label fw-bold text-secondary">{{ __('Descripción') }}</label>
                    <textarea name="description" id="description" class="form-control border-light-subtle @error('description') is-invalid @enderror"
                              rows="3" maxlength="500"
                              placeholder="Ingrese una descripción opcional">{{ old('description', $scrapCategory->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('scrap-categories.index') }}" class="btn btn-outline-secondary rounded-3">
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
    </style>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            // Foco automático al primer campo
            setTimeout(function() {
                $('#name').focus();
            }, 100);
        });
    </script>
@stop
