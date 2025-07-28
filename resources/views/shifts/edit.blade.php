@extends('adminlte::page')

@section('title', 'Editar Turno')

@section('content_header')
    <h1>{{ __('Editar Turno') }}</h1>
@stop

@section('content')
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="card-body">
            <form action="{{ route('shifts.update', $shift) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row mb-3">
                    <div class="col-md-3">
                        <label for="abbreviation" class="form-label fw-bold text-secondary">{{ __('Abreviatura') }}</label>
                        <input type="text" name="abbreviation" id="abbreviation"
                               class="form-control border-light-subtle @error('abbreviation') is-invalid @enderror"
                               value="{{ old('abbreviation', $shift->abbreviation) }}" required maxlength="10">
                        @error('abbreviation')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <label for="name" class="form-label fw-bold text-secondary">{{ __('Nombre') }}</label>
                        <input type="text" name="name" id="name"
                               class="form-control border-light-subtle @error('name') is-invalid @enderror"
                               value="{{ old('name', $shift->name) }}" required maxlength="100">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <label for="start_time" class="form-label fw-bold text-secondary">{{ __('Hora Inicio') }}</label>
                        <input type="time" name="start_time" id="start_time"
                               class="form-control border-light-subtle @error('start_time') is-invalid @enderror"
                               value="{{ old('start_time', $shift->start_time) }}" required>
                        @error('start_time')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <label for="end_time" class="form-label fw-bold text-secondary">{{ __('Hora Fin') }}</label>
                        <input type="time" name="end_time" id="end_time"
                               class="form-control border-light-subtle @error('end_time') is-invalid @enderror"
                               value="{{ old('end_time', $shift->end_time) }}" required>
                        @error('end_time')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-4">
                    <label for="description" class="form-label fw-bold text-secondary">{{ __('Descripción') }}</label>
                    <textarea name="description" id="description"
                              class="form-control border-light-subtle @error('description') is-invalid @enderror"
                              rows="3" maxlength="255">{{ old('description', $shift->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('shifts.index') }}" class="btn btn-outline-secondary rounded-3">
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
    @parent
    <!-- Estilos heredados de create -->
@stop

@section('js')
    <script>
        // Foco automático al primer campo
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                document.getElementById('abbreviation').focus();
            }, 100);
        });
    </script>
@stop
