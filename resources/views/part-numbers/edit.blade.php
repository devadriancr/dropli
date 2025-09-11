@extends('adminlte::page')

@section('title', 'Editar Número de Parte')

@section('content_header')
    <h1>{{ __('Editar Número de Parte') }}</h1>
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
        <div class="card-header bg-white border-0 py-3">
            <h5 class="card-title mb-0 fw-bold">{{ $partNumber->number }} - {{ $partNumber->name }}</h5>
        </div>

        <div class="card-body">
            <!-- Información del Part Number (Solo lectura) -->
            <div class="row mb-4">
                <div class="col-12">
                    <h6 class="text-secondary fw-bold mb-3">INFORMACIÓN DEL NÚMERO DE PARTE</h6>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold text-secondary">Número</label>
                    <input type="text" class="form-control bg-light border-light-subtle" value="{{ $partNumber->number }}" readonly>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold text-secondary">Nombre</label>
                    <input type="text" class="form-control bg-light border-light-subtle" value="{{ $partNumber->name ?? '-' }}" readonly>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold text-secondary">Clase de Ítem</label>
                    <input type="text" class="form-control bg-light border-light-subtle"
                           value="{{ $partNumber->itemClass ? $partNumber->itemClass->abbreviation . ' - ' . $partNumber->itemClass->name : '-' }}" readonly>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold text-secondary">Centro de Trabajo</label>
                    <input type="text" class="form-control bg-light border-light-subtle"
                           value="{{ $partNumber->workCenter ? $partNumber->workCenter->number . ' - ' . $partNumber->workCenter->name : '-' }}" readonly>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold text-secondary">Empaque Estándar</label>
                    <input type="text" class="form-control bg-light border-light-subtle"
                           value="{{ $partNumber->standardPack ? $partNumber->standardPack->name : '-' }}" readonly>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold text-secondary">Tasa de Producción</label>
                    <input type="text" class="form-control bg-light border-light-subtle"
                           value="{{ $partNumber->production_rate ?? '-' }}" readonly>
                </div>
            </div>

            <hr class="my-4">

            <!-- Formulario para Atributos -->
            <form action="{{ route('part-numbers.update', $partNumber) }}" method="POST" id="attributesForm">
                @csrf
                @method('PATCH')

                <div class="row mb-4">
                    <div class="col-12">
                        <h6 class="text-secondary fw-bold mb-3">ATRIBUTOS PERSONALIZADOS</h6>
                    </div>
                </div>

                <!-- Atributos Existentes -->
                <div id="existing-attributes">
                    @forelse($partNumber->attributes as $index => $attribute)
                        <div class="row mb-3 attribute-row">
                            <div class="col-md-4">
                                <label class="form-label fw-bold text-secondary">Nombre del Atributo</label>
                                <input type="text" name="attributes[{{ $index }}][key]"
                                       class="form-control border-light-subtle"
                                       value="{{ $attribute->attribute_key }}" readonly>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold text-secondary">Valor</label>
                                <input type="text" name="attributes[{{ $index }}][value]"
                                       class="form-control border-light-subtle @error('attributes.'.$index.'.value') is-invalid @enderror"
                                       value="{{ old('attributes.'.$index.'.value', $attribute->attribute_value) }}"
                                       required>
                                @error('attributes.'.$index.'.value')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold text-secondary">Tipo de Dato</label>
                                <input type="text" class="form-control border-light-subtle bg-light"
                                       value="{{ $attribute->data_type }}" readonly>
                                <input type="hidden" name="attributes[{{ $index }}][data_type]" value="{{ $attribute->data_type }}">
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="button" class="btn btn-outline-danger btn-sm w-100 remove-attribute-btn">
                                    <i class="fas fa-trash"></i> Eliminar
                                </button>
                                <input type="hidden" name="delete_attributes[]" class="delete-flag" value="" disabled>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-3">
                            <i class="fas fa-info-circle text-muted fa-2x mb-2"></i>
                            <p class="text-muted">No hay atributos personalizados para este número de parte.</p>
                        </div>
                    @endforelse
                </div>

                <hr class="my-4">

                <!-- Selector para Agregar Atributos Existentes -->
                <div class="row mb-3">
                    <div class="col-12">
                        <h6 class="text-secondary fw-bold mb-3">AGREGAR ATRIBUTOS EXISTENTES</h6>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold text-secondary">Seleccionar Atributo</label>
                        <select id="existing-attribute-selector" class="form-control border-light-subtle">
                            <option value="">Seleccione un atributo...</option>
                            @foreach($existingAttributes as $attributeKey => $dataType)
                                <option value="{{ $attributeKey }}" data-type="{{ $dataType }}">{{ $attributeKey }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold text-secondary">Valor</label>
                        <input type="text" id="existing-attribute-value" class="form-control border-light-subtle" placeholder="Ingrese el valor">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold text-secondary">Tipo de Dato</label>
                        <input type="text" id="existing-attribute-type-display" class="form-control border-light-subtle bg-light" readonly>
                        <input type="hidden" id="existing-attribute-type" name="existing_attribute_type">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="button" id="add-existing-attribute-btn" class="btn btn-outline-primary w-100">
                            <i class="fas fa-plus"></i> Agregar
                        </button>
                    </div>
                </div>

                <hr class="my-3">

                <!-- Nuevos Atributos -->
                <div class="row mb-3">
                    <div class="col-12">
                        <h6 class="text-secondary fw-bold mb-3">CREAR NUEVOS ATRIBUTOS</h6>
                    </div>
                </div>

                <div id="new-attributes-container">
                    <!-- Los nuevos atributos se agregarán aquí dinámicamente -->
                </div>

                <div class="row mb-4">
                    <div class="col-12">
                        <button type="button" id="add-new-attribute-btn" class="btn btn-outline-success">
                            <i class="fas fa-plus"></i> Agregar Nuevo Atributo
                        </button>
                    </div>
                </div>

                <!-- Botones de Acción -->
                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('part-numbers.index') }}" class="btn btn-outline-secondary rounded-3">
                        <i class="fas fa-arrow-left me-2"></i> Volver
                    </a>
                    <button type="submit" class="btn btn-primary rounded-3">
                        <i class="fas fa-save me-2"></i> Guardar Cambios
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
        .card,
        .btn,
        .form-control,
        .form-label,
        h1, h5, h6 {
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

        .form-control[readonly] {
            background-color: #f8f9fa !important;
            opacity: 1;
        }

        .form-label {
            font-size: 0.9rem !important;
            margin-bottom: 0.5rem !important;
            font-weight: 600 !important;
        }

        .btn {
            border-radius: 8px !important;
            padding: 0.5rem 1rem !important;
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

        .btn-sm {
            padding: 0.35rem 0.75rem !important;
            font-size: 0.85rem !important;
        }

        .rounded-3 {
            border-radius: 12px !important;
        }

        .gap-2 {
            gap: 0.5rem;
        }

        .attribute-row {
            background-color: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            border: 1px solid #e9ecef;
        }

        .attribute-row.marked-for-deletion {
            opacity: 0.5;
            background-color: #fff5f5;
            border-color: #fed7d7;
        }

        .invalid-feedback {
            font-size: 0.85rem !important;
        }

        hr {
            border-color: #e9ecef;
            opacity: 1;
        }

        .alert {
            border-radius: 8px;
        }
    </style>
@stop

@section('js')
    <script>
        let newAttributeIndex = 0;
        let existingAttributeIndex = {{ $partNumber->attributes->count() }};

        $(document).ready(function() {
            // Actualizar tipo de dato cuando se selecciona un atributo existente
            $('#existing-attribute-selector').change(function() {
                const selectedOption = $(this).find('option:selected');
                const dataType = selectedOption.data('type');

                if (dataType) {
                    $('#existing-attribute-type-display').val(dataType);
                    $('#existing-attribute-type').val(dataType);
                } else {
                    $('#existing-attribute-type-display').val('');
                    $('#existing-attribute-type').val('');
                }
            });

            // Agregar nuevo atributo
            $('#add-new-attribute-btn').click(function() {
                addNewAttributeRow();
            });

            // Agregar atributo existente
            $('#add-existing-attribute-btn').click(function() {
                const selectedAttribute = $('#existing-attribute-selector').val();
                const attributeValue = $('#existing-attribute-value').val();
                const attributeType = $('#existing-attribute-type').val();

                if (selectedAttribute && attributeValue && attributeType) {
                    addExistingAttributeRow(selectedAttribute, attributeValue, attributeType);
                    // Limpiar campos
                    $('#existing-attribute-selector').val('');
                    $('#existing-attribute-value').val('');
                    $('#existing-attribute-type-display').val('');
                    $('#existing-attribute-type').val('');
                } else {
                    alert('Por favor complete todos los campos para el atributo existente.');
                }
            });

            // Eliminar atributo
            $(document).on('click', '.remove-attribute-btn', function() {
                const row = $(this).closest('.attribute-row');
                const deleteFlag = row.find('.delete-flag');

                if (row.hasClass('new-attribute')) {
                    // Es un atributo nuevo, simplemente eliminar la fila
                    row.remove();
                } else {
                    // Es un atributo existente, marcar para eliminación
                    if (row.hasClass('marked-for-deletion')) {
                        // Restaurar
                        row.removeClass('marked-for-deletion');
                        deleteFlag.prop('disabled', true);
                        deleteFlag.val('');
                        $(this).html('<i class="fas fa-trash"></i> Eliminar');
                        $(this).removeClass('btn-warning').addClass('btn-outline-danger');
                    } else {
                        // Marcar para eliminación
                        row.addClass('marked-for-deletion');
                        const attributeKey = row.find('input[name*="[key]"]').val();
                        deleteFlag.prop('disabled', false);
                        deleteFlag.val(attributeKey);
                        $(this).html('<i class="fas fa-undo"></i> Restaurar');
                        $(this).removeClass('btn-outline-danger').addClass('btn-warning');
                    }
                }
            });

            // Cerrar alertas automáticamente
            setTimeout(() => {
                $('.alert').fadeOut();
            }, 5000);
        });

        function addNewAttributeRow() {
            const html = `
                <div class="row mb-3 attribute-row new-attribute">
                    <div class="col-md-4">
                        <label class="form-label fw-bold text-secondary">Nombre del Atributo</label>
                        <input type="text" name="new_attributes[${newAttributeIndex}][key]"
                               class="form-control border-light-subtle"
                               placeholder="Ej: tiempo_ciclo" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold text-secondary">Valor</label>
                        <input type="text" name="new_attributes[${newAttributeIndex}][value]"
                               class="form-control border-light-subtle"
                               placeholder="Ej: 30" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold text-secondary">Tipo de Dato</label>
                        <select name="new_attributes[${newAttributeIndex}][data_type]"
                                class="form-control border-light-subtle" required>
                            <option value="string">Texto</option>
                            <option value="integer">Número Entero</option>
                            <option value="double">Número Decimal</option>
                            <option value="boolean">Verdadero/Falso</option>
                            <option value="array">Array/Lista</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="button" class="btn btn-outline-danger btn-sm w-100 remove-attribute-btn">
                            <i class="fas fa-trash"></i> Eliminar
                        </button>
                    </div>
                </div>
            `;

            $('#new-attributes-container').append(html);
            newAttributeIndex++;
        }

        function addExistingAttributeRow(attributeName, attributeValue, attributeType) {
            const html = `
                <div class="row mb-3 attribute-row">
                    <div class="col-md-4">
                        <label class="form-label fw-bold text-secondary">Nombre del Atributo</label>
                        <input type="text" name="attributes[${existingAttributeIndex}][key]"
                               class="form-control border-light-subtle bg-light"
                               value="${attributeName}" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold text-secondary">Valor</label>
                        <input type="text" name="attributes[${existingAttributeIndex}][value]"
                               class="form-control border-light-subtle"
                               value="${attributeValue}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold text-secondary">Tipo de Dato</label>
                        <input type="text" class="form-control border-light-subtle bg-light"
                               value="${attributeType}" readonly>
                        <input type="hidden" name="attributes[${existingAttributeIndex}][data_type]" value="${attributeType}">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="button" class="btn btn-outline-danger btn-sm w-100 remove-attribute-btn">
                            <i class="fas fa-trash"></i> Eliminar
                        </button>
                        <input type="hidden" name="delete_attributes[]" class="delete-flag" value="" disabled>
                    </div>
                </div>
            `;

            $('#existing-attributes').append(html);
            existingAttributeIndex++;
        }
    </script>
@stop
