<x-guest-layout>
    <div class="bg-gray-100 dark:bg-gray-900 min-h-screen py-8">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="w-full max-w-6xl">
                @if(session('success'))
                    <div class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            {{ session('success') }}
                        </div>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                            </svg>
                            {{ session('error') }}
                        </div>
                    </div>
                @endif

                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-8">
                    <div class="text-center mb-6">
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Registro de Scrap</h1>
                    </div>
                    <form action="{{ route('guest.scrap-records.store') }}" method="POST" id="scrapForm" class="space-y-6">
                        @csrf
                        <div>
                            <label for="part_number_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Part Number *
                            </label>
                            <select name="part_number_id" id="part_number_id"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white @error('part_number_id') border-red-500 @enderror"
                                    required>
                                <option value="">Buscar part number...</option>
                                @foreach($partNumbers as $partNumber)
                                    <option value="{{ $partNumber->id }}" {{ old('part_number_id') == $partNumber->id ? 'selected' : '' }}>
                                        {{ $partNumber->number }}
                                    </option>
                                @endforeach
                            </select>
                            @error('part_number_id')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="scrap_reason_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Razón de Scrap *
                            </label>
                            <select name="scrap_reason_id" id="scrap_reason_id"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white @error('scrap_reason_id') border-red-500 @enderror"
                                    required>
                                <option value="">Seleccione una razón</option>
                                @foreach($scrapReasons as $reason)
                                    <option value="{{ $reason->id }}" {{ old('scrap_reason_id') == $reason->id ? 'selected' : '' }}>
                                        {{ $reason->code }} - {{ $reason->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('scrap_reason_id')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="quantity" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Cantidad *
                            </label>
                            <input type="number" name="quantity" id="quantity" min="1" step="1"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white @error('quantity') border-red-500 @enderror"
                                   value="{{ old('quantity') }}" required
                                   placeholder="Ingrese la cantidad">
                            @error('quantity')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex justify-center pt-4">
                            <button type="submit" id="submitBtn"
                                    class="w-full flex items-center justify-center gap-2 px-8 py-3 min-w-48 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition duration-200 ease-in-out">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span id="submitText">Guardar Registro</span>
                                <div id="submitSpinner" class="hidden ml-2">
                                    <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-white"></div>
                                </div>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-xl shadow-2xl p-8 max-w-sm w-full mx-4">
            <div class="flex flex-col items-center justify-center space-y-4">
                <!-- Spinner -->
                <div class="animate-spin rounded-full h-16 w-16 border-b-2 border-gray-700"></div>

                <!-- Texto -->
                <div class="text-center">
                    <p class="text-lg font-semibold text-gray-800">Procesando...</p>
                    <p class="text-sm text-gray-600 mt-2">Guardando información, por favor espere</p>
                </div>

                <!-- Barra de progreso opcional -->
                <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                    <div class="bg-gray-600 h-2 rounded-full animate-pulse w-3/4"></div>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Estilos para Select2 */
        .select2-container--default .select2-selection--single {
            height: 50px !important;
            border: 1px solid #d1d5db !important;
            border-radius: 8px !important;
            padding: 0 !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 48px !important;
            padding-left: 16px !important;
            padding-right: 20px !important;
            font-size: 0.95rem !important;
            color: #374151 !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__placeholder {
            color: #9ca3af !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 48px !important;
            right: 8px !important;
        }

        .select2-container--default.select2-container--focus .select2-selection--single {
            border-color: #3b82f6 !important;
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2) !important;
            outline: none !important;
        }

        .select2-dropdown {
            border: 1px solid #d1d5db !important;
            border-radius: 8px !important;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1) !important;
        }

        .select2-results__option {
            padding: 8px 16px !important;
            font-size: 0.95rem !important;
        }

        .select2-results__option--highlighted {
            background-color: #3b82f6 !important;
        }

        .select2-search--dropdown .select2-search__field {
            border: 1px solid #d1d5db !important;
            border-radius: 6px !important;
            padding: 8px 12px !important;
        }

        /* Estilos para modo oscuro */
        .dark .select2-container--default .select2-selection--single {
            background-color: #374151 !important;
            border-color: #4b5563 !important;
        }

        .dark .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #fff !important;
        }

        .dark .select2-dropdown {
            background-color: #374151 !important;
            border-color: #4b5563 !important;
        }

        .dark .select2-results__option {
            color: #fff !important;
        }

        .dark .select2-results__option--highlighted {
            background-color: #3b82f6 !important;
        }

        .dark .select2-search--dropdown .select2-search__field {
            background-color: #4b5563 !important;
            border-color: #6b7280 !important;
            color: #fff !important;
        }
    </style>

    <!-- CDN Resources -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-rc.0/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-rc.0/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {
            // Elementos para el loading
            const submitBtn = document.getElementById('submitBtn');
            const submitText = document.getElementById('submitText');
            const submitSpinner = document.getElementById('submitSpinner');
            const loadingOverlay = document.getElementById('loadingOverlay');
            const scrapForm = document.getElementById('scrapForm');

            // Función para mostrar loading
            function showLoading() {
                loadingOverlay.classList.remove('hidden');
                loadingOverlay.classList.add('flex');
            }

            // Función para ocultar loading
            function hideLoading() {
                loadingOverlay.classList.add('hidden');
                loadingOverlay.classList.remove('flex');
            }

            // Función para mostrar loading en el botón de envío
            function showSubmitLoading() {
                submitText.classList.add('hidden');
                submitSpinner.classList.remove('hidden');
                submitBtn.disabled = true;
            }

            // Función para ocultar loading en el botón de envío
            function hideSubmitLoading() {
                submitText.classList.remove('hidden');
                submitSpinner.classList.add('hidden');
                submitBtn.disabled = false;
            }

            // Inicializar Select2 para Part Number con búsqueda mejorada
            $('#part_number_id').select2({
                placeholder: 'Buscar por número de parte...',
                allowClear: true,
                width: '100%',
                matcher: function(params, data) {
                    // Si no hay término de búsqueda, mostrar todas las opciones
                    if ($.trim(params.term) === '') {
                        return data;
                    }

                    // Buscar en el texto completo (número y nombre)
                    var text = data.text.toLowerCase();
                    var term = params.term.toLowerCase();

                    // Buscar coincidencias parciales
                    if (text.indexOf(term) > -1) {
                        return data;
                    }

                    return null;
                }
            });

            // También se puede aplicar Select2 a scrap_reason_id si se necesita búsqueda
            $('#scrap_reason_id').select2({
                placeholder: 'Seleccione una razón...',
                allowClear: true,
                width: '100%'
            });

            // Focus automático en el primer campo
            setTimeout(function() {
                $('#part_number_id').select2('focus');
            }, 100);

            // Permitir enviar el formulario con Enter en el campo de cantidad
            $('#quantity').on('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    // Mostrar loading antes de enviar
                    showSubmitLoading();
                    showLoading();
                    $('#scrapForm').submit();
                }
            });

            // Manejar el envío del formulario
            scrapForm.addEventListener('submit', function(e) {
                // Mostrar loading en el botón y overlay general
                showSubmitLoading();
                showLoading();

                // Opcional: prevenir envío duplicado
                let formSubmitted = false;

                if (!formSubmitted) {
                    formSubmitted = true;
                    // Permitir que el formulario se envíe normalmente
                    return true;
                } else {
                    e.preventDefault();
                    return false;
                }
            });

            // También manejar el evento submit con jQuery para mayor compatibilidad
            $('#scrapForm').on('submit', function() {
                showSubmitLoading();
                showLoading();
            });
        });
    </script>
</x-guest-layout>
