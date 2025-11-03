<x-guest-layout>
    <div class="min-h-screen flex flex-col items-center justify-center p-4 bg-gray-50">
        <!-- Header -->
        <div class="w-full max-w-3xl flex justify-between items-center mb-2">
            @php
                $origin = session('part_number_entry_origin');
                $isEntry = true; // Por defecto entrada
                if ($origin) {
                    $isEntry = str_contains($origin, 'entry-scan') || !str_contains($origin, 'exit-scan');
                }
                $title = $isEntry ? 'Entrada de Material - Captura Manual' : 'Salida de Material - Captura Manual';
                $backRoute = $origin ?: route('production-records.entry-scan');
            @endphp

            <h1 class="text-3xl font-bold text-gray-900">{{ $title }}</h1>
            <a href="{{ $backRoute }}"
                class="inline-flex items-center justify-center gap-2 w-full max-w-[200px] py-2 text-white bg-gray-700 hover:bg-gray-800 rounded-lg text-sm transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" />
                </svg>
                Regresar
            </a>
        </div>

        <!-- Card principal -->
        <div class="w-full max-w-3xl bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="p-8">
                <form method="POST" action="{{ route('production-records.store-part-number') }}" id="scanForm">
                    @csrf

                    <div class="flex flex-col gap-6 mb-6">
                        <div>
                            <label for="partNumber" class="block text-lg font-medium text-gray-700 mb-3">
                                Número de Parte
                            </label>
                            <div class="relative">
                                <select name="partNumber" id="partNumber" required
                                    class="w-full px-5 py-4 text-lg border-2 border-gray-300 rounded-lg focus:outline-none focus:border-gray-500 transition-colors appearance-none bg-white">
                                    <option value="">Seleccione un número de parte</option>
                                    @foreach ($partNumbers as $part)
                                        <option value="{{ $part->number }}"
                                            {{ old('partNumber') == $part->number ? 'selected' : '' }}>
                                            {{ $part->number }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div>
                            <label for="quantity" class="block text-lg font-medium text-gray-700 mb-3">
                                Cantidad
                            </label>
                            <div class="relative">
                                <input type="number" id="quantity" name="quantity"
                                    class="w-full px-5 py-4 text-lg border-2 border-gray-300 rounded-lg focus:outline-none focus:border-gray-500 transition-colors"
                                    placeholder="Ingrese la cantidad..." min="1" required
                                    value="{{ old('quantity') }}" />
                            </div>
                        </div>
                    </div>

                    <!-- Botón de envío -->
                    <div class="mt-8">
                        <button type="submit" id="submitBtn"
                            class="w-full px-6 py-4 text-white bg-gray-700 hover:bg-gray-800 rounded-lg font-medium text-lg transition-colors flex items-center justify-center">
                            <span id="submitText">Registrar</span>
                            <div id="submitSpinner" class="hidden ml-2">
                                <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-white"></div>
                            </div>
                        </button>
                    </div>

                    {{-- Mensajes flash --}}
                    @if (session('success'))
                        <div class="mt-4 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg text-base">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="mt-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg text-base">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if (session('warning'))
                        <div
                            class="mt-4 p-4 bg-yellow-50 border border-yellow-200 text-yellow-700 rounded-lg text-base">
                            {{ session('warning') }}
                        </div>
                    @endif

                    {{-- Errores de validación --}}
                    @if ($errors->any())
                        <div class="mt-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg text-base">
                            @foreach ($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    @endif
                </form>
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
            const scanForm = document.getElementById('scanForm');

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

            // Inicializar Select2 para el número de parte
            $('#partNumber').select2({
                placeholder: 'Buscar número de parte...',
                allowClear: false,
                width: '100%',
                matcher: function(params, data) {
                    // Si no hay término de búsqueda, mostrar todas las opciones
                    if ($.trim(params.term) === '') {
                        return data;
                    }

                    // Buscar en el texto completo
                    var text = data.text.toLowerCase();
                    var term = params.term.toLowerCase();

                    // Buscar coincidencias parciales
                    if (text.indexOf(term) > -1) {
                        return data;
                    }

                    return null;
                }
            });

            // Mantener focus en el select de número de parte
            $('#partNumber').select2('focus');

            // Permitir enviar el formulario con Enter en el campo de cantidad
            $('#quantity').on('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    // Mostrar loading antes de enviar
                    showSubmitLoading();
                    showLoading();
                    $('#scanForm').submit();
                }
            });

            // Al seleccionar un número de parte, pasar a cantidad
            $('#partNumber').on('change', function() {
                if ($(this).val()) {
                    $('#quantity').focus();
                }
            });

            // También permitir Enter en el select para pasar a cantidad
            $('#partNumber').on('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    $('#quantity').focus();
                }
            });

            // Manejar el envío del formulario
            scanForm.addEventListener('submit', function(e) {
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
            $('#scanForm').on('submit', function() {
                showSubmitLoading();
                showLoading();
            });
        });
    </script>

    <style>
        /* Estilos para Select2 adaptados al diseño actual */
        .select2-container--default .select2-selection--single {
            height: 68px !important;
            border: 2px solid #d1d5db !important;
            border-radius: 8px !important;
            padding: 0 !important;
            font-size: 1.125rem !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 66px !important;
            padding-left: 20px !important;
            padding-right: 30px !important;
            font-size: 1.125rem !important;
            color: #374151 !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__placeholder {
            color: #9ca3af !important;
            font-size: 1.125rem !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 66px !important;
            right: 12px !important;
            width: 30px !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow b {
            border-color: #6b7280 transparent transparent transparent !important;
            border-width: 8px 6px 0 6px !important;
        }

        .select2-container--default.select2-container--open .select2-selection--single .select2-selection__arrow b {
            border-color: transparent transparent #6b7280 transparent !important;
            border-width: 0 6px 8px 6px !important;
        }

        .select2-container--default.select2-container--focus .select2-selection--single {
            border-color: #6b7280 !important;
            box-shadow: 0 0 0 2px rgba(107, 114, 128, 0.2) !important;
            outline: none !important;
        }

        .select2-dropdown {
            border: 2px solid #d1d5db !important;
            border-radius: 8px !important;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1) !important;
            margin-top: 4px !important;
        }

        .select2-results__option {
            padding: 12px 20px !important;
            font-size: 1.125rem !important;
            line-height: 1.5 !important;
        }

        .select2-results__option--highlighted {
            background-color: #4b5563 !important;
            color: white !important;
        }

        .select2-results__option[aria-selected="true"] {
            background-color: #f3f4f6 !important;
            color: #374151 !important;
        }

        .select2-search--dropdown .select2-search__field {
            border: 2px solid #d1d5db !important;
            border-radius: 6px !important;
            padding: 12px !important;
            font-size: 1.125rem !important;
            margin: 8px !important;
            width: calc(100% - 16px) !important;
        }

        .select2-results {
            padding: 8px 0 !important;
        }

        /* Ocultar la flecha nativa del select */
        select#partNumber {
            background-image: none !important;
            padding-right: 20px !important;
        }
    </style>
</x-guest-layout>
