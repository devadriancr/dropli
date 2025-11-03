@extends('adminlte::page')

@section('title', 'Crear Usuario')

@section('content_header')
    <h1>{{ __('Crear Usuario') }}</h1>
@stop

@section('content')
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="card-body">
            <form action="{{ route('users.store') }}" method="POST">
                @csrf

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="name" class="form-label fw-bold text-secondary">{{ __('Nombre') }}</label>
                        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name') }}" required maxlength="255">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="email" class="form-label fw-bold text-secondary">{{ __('Email') }}</label>
                        <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email') }}" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="role" class="form-label fw-bold text-secondary">{{ __('Rol') }}</label>
                        <select name="role" id="role" class="form-control @error('role') is-invalid @enderror" required>
                            <option value="">Seleccionar rol</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}" {{ old('role') == $role->id ? 'selected' : '' }}>
                                    {{ $role->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('role')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="password" class="form-label fw-bold text-secondary">{{ __('Contraseña') }}</label>
                        <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror"
                               required>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="password_confirmation" class="form-label fw-bold text-secondary">{{ __('Confirmar Contraseña') }}</label>
                        <input type="password" name="password_confirmation" id="password_confirmation"
                               class="form-control" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold text-secondary">{{ __('Estaciones de Trabajo') }}</label>
                    <div class="work-centers-container border rounded p-3 bg-light">
                        <div class="row">
                            @forelse($workCenters as $workCenter)
                                <div class="col-md-4 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox"
                                               name="work_centers[]"
                                               id="work_center_{{ $workCenter->id }}"
                                               value="{{ $workCenter->id }}"
                                               {{ old('work_centers') && in_array($workCenter->id, old('work_centers')) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="work_center_{{ $workCenter->id }}">
                                            <span class="fw-bold">{{ $workCenter->number }}</span> - {{ $workCenter->name }}
                                        </label>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12">
                                    <p class="text-muted">{{ __('No hay estaciones de trabajo disponibles') }}</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                    @error('work_centers')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary rounded-3">
                        Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary rounded-3">
                        Crear Usuario
                    </button>
                </div>
            </form>
        </div>
    </div>
@stop

@section('css')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">

    <style>
        body, .main-header, .main-sidebar, .content-wrapper, .card, .btn, .form-control, .form-label, h1, h2, h3, h4, h5, h6 {
            font-family: 'Roboto', sans-serif !important;
        }

        .form-control {
            border-radius: 8px !important;
            padding: 0.5rem 1rem !important;
        }

        .form-label {
            font-size: 0.9rem !important;
            margin-bottom: 0.5rem !important;
            font-weight: 600 !important;
        }

        .btn {
            border-radius: 8px !important;
            font-weight: 500;
        }

        .work-centers-container {
            max-height: 200px;
            overflow-y: auto;
        }

        .form-check-input:checked {
            background-color: #007bff;
            border-color: #007bff;
        }
    </style>
@stop
