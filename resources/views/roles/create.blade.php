@extends('adminlte::page')

@section('title', 'Crear Rol')

@section('content_header')
    <h1>{{ __('Crear Rol') }}</h1>
@stop

@section('content')
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="card-body">
            <form action="{{ route('roles.store') }}" method="POST">
                @csrf

                <div class="row mb-4">
                    <div class="col-md-6">
                        <label for="name" class="form-label fw-bold text-secondary">{{ __('Nombre del Rol') }}</label>
                        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name') }}" required maxlength="255" placeholder="Ej: Supervisor">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold text-secondary mb-3">{{ __('Permisos') }}</label>

                    <!-- Botones de selección rápida -->
                    <div class="mb-3">
                        <button type="button" class="btn btn-sm btn-outline-primary me-2" id="selectAll">
                            Seleccionar todos
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="deselectAll">
                            Deseleccionar todos
                        </button>
                    </div>

                    <div class="permissions-container">
                        @php
                            // Organizar permisos por categorías
                            $permissionGroups = [
                                'Crear' => [],
                                'Editar' => [],
                                'Actualizar' => [],
                                'Eliminar' => [],
                                'Otros' => []
                            ];

                            foreach($permissions as $module => $modulePermissions) {
                                foreach($modulePermissions as $permission) {
                                    $permissionName = strtolower($permission->name);

                                    if (str_contains($permissionName, 'create') || str_contains($permissionName, 'crear')) {
                                        $permissionGroups['Crear'][] = $permission;
                                    } elseif (str_contains($permissionName, 'edit') || str_contains($permissionName, 'editar')) {
                                        $permissionGroups['Editar'][] = $permission;
                                    } elseif (str_contains($permissionName, 'update') || str_contains($permissionName, 'actualizar')) {
                                        $permissionGroups['Actualizar'][] = $permission;
                                    } elseif (str_contains($permissionName, 'delete') || str_contains($permissionName, 'eliminar')) {
                                        $permissionGroups['Eliminar'][] = $permission;
                                    } else {
                                        $permissionGroups['Otros'][] = $permission;
                                    }
                                }
                            }
                        @endphp

                        @foreach($permissionGroups as $groupName => $groupPermissions)
                            @if(count($groupPermissions) > 0)
                                <div class="permission-group mb-4">
                                    <div class="card border-0 bg-light">
                                        <div class="card-header bg-white border-bottom-0 py-3">
                                            <div class="form-check mb-0">
                                                <input class="form-check-input group-checkbox" type="checkbox"
                                                       id="group_{{ $loop->index }}"
                                                       data-group="{{ $groupName }}">
                                                <label class="form-check-label fw-bold text-primary mb-0" for="group_{{ $loop->index }}">
                                                    {{ $groupName }}
                                                </label>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                @foreach($groupPermissions as $permission)
                                                    <div class="col-md-4 mb-3">
                                                        <div class="form-check">
                                                            <input class="form-check-input permission-checkbox"
                                                                   type="checkbox"
                                                                   name="permissions[]"
                                                                   id="permission_{{ $permission->id }}"
                                                                   value="{{ $permission->id }}"
                                                                   data-group="{{ $groupName }}"
                                                                   {{ old('permissions') && in_array($permission->id, old('permissions')) ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="permission_{{ $permission->id }}">
                                                                {{ $permission->name }}
                                                            </label>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                    @error('permissions')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary rounded-3">
                        Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary rounded-3">
                        Crear Rol
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

        .permissions-container {
            max-height: 600px;
            overflow-y: auto;
        }

        .permission-group {
            border-radius: 8px;
        }

        .form-check-input {
            width: 1.1em;
            height: 1.1em;
            margin-top: 0.1em;
        }

        .form-check-input:checked {
            background-color: #007bff;
            border-color: #007bff;
        }

        .form-check-label {
            cursor: pointer;
            margin-left: 0.5rem;
        }

        .gap-2 {
            gap: 0.5rem;
        }
    </style>
@stop

@section('js')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Seleccionar/deseleccionar todos
            document.getElementById('selectAll').addEventListener('click', function() {
                document.querySelectorAll('.permission-checkbox').forEach(checkbox => {
                    checkbox.checked = true;
                });
                document.querySelectorAll('.group-checkbox').forEach(checkbox => {
                    checkbox.checked = true;
                });
            });

            document.getElementById('deselectAll').addEventListener('click', function() {
                document.querySelectorAll('.permission-checkbox').forEach(checkbox => {
                    checkbox.checked = false;
                });
                document.querySelectorAll('.group-checkbox').forEach(checkbox => {
                    checkbox.checked = false;
                });
            });

            // Checkbox de grupo selecciona/deselecciona todos sus permisos
            document.querySelectorAll('.group-checkbox').forEach(groupCheckbox => {
                groupCheckbox.addEventListener('change', function() {
                    const groupName = this.dataset.group;
                    const isChecked = this.checked;

                    document.querySelectorAll(`.permission-checkbox[data-group="${groupName}"]`).forEach(permissionCheckbox => {
                        permissionCheckbox.checked = isChecked;
                    });
                });
            });

            // Actualizar checkbox de grupo cuando se seleccionan/deseleccionan permisos individuales
            document.querySelectorAll('.permission-checkbox').forEach(permissionCheckbox => {
                permissionCheckbox.addEventListener('change', function() {
                    const groupName = this.dataset.group;
                    const groupCheckbox = document.querySelector(`.group-checkbox[data-group="${groupName}"]`);
                    const groupPermissions = document.querySelectorAll(`.permission-checkbox[data-group="${groupName}"]`);
                    const checkedPermissions = document.querySelectorAll(`.permission-checkbox[data-group="${groupName}"]:checked`);

                    if (checkedPermissions.length === groupPermissions.length) {
                        groupCheckbox.checked = true;
                        groupCheckbox.indeterminate = false;
                    } else if (checkedPermissions.length > 0) {
                        groupCheckbox.checked = false;
                        groupCheckbox.indeterminate = true;
                    } else {
                        groupCheckbox.checked = false;
                        groupCheckbox.indeterminate = false;
                    }
                });
            });

            // Inicializar estado de checkboxes de grupos
            document.querySelectorAll('.group-checkbox').forEach(groupCheckbox => {
                const groupName = groupCheckbox.dataset.group;
                const groupPermissions = document.querySelectorAll(`.permission-checkbox[data-group="${groupName}"]`);
                const checkedPermissions = document.querySelectorAll(`.permission-checkbox[data-group="${groupName}"]:checked`);

                if (checkedPermissions.length === groupPermissions.length && groupPermissions.length > 0) {
                    groupCheckbox.checked = true;
                } else if (checkedPermissions.length > 0) {
                    groupCheckbox.indeterminate = true;
                }
            });
        });
    </script>
@stop
