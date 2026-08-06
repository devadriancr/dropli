<x-guest-layout>
    <div class="min-h-screen flex flex-col items-center justify-center p-4 bg-gray-50">
        <!-- Header -->
        <div class="w-full max-w-3xl flex flex-col sm:flex-row justify-between items-center mb-6 gap-4">
            @php
                $origin = session('part_number_entry_origin');
                $isEntry = true; // Por defecto entrada
                if ($origin) {
                    $isEntry = str_contains($origin, 'entry-scan') || !str_contains($origin, 'exit-scan');
                }
                $title = $isEntry ? 'Entrada de Material - Captura Manual' : 'Salida de Material - Captura Manual';
                $backRoute = $origin ?: route('production-records.entry-scan');
                $accentBg = $isEntry ? 'bg-blue-100' : 'bg-green-100';
                $accentText = $isEntry ? 'text-blue-600' : 'text-green-600';
                $accentBtn = $isEntry ? 'bg-blue-600 hover:bg-blue-700' : 'bg-green-600 hover:bg-green-700';
            @endphp

            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-full {{ $accentBg }} flex items-center justify-center flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 {{ $accentText }}" viewBox="0 0 20 20" fill="currentColor">
                        @if ($isEntry)
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v3.586L7.707 9.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 10.586V7z"
                                clip-rule="evenodd" />
                        @else
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v3.586L7.707 9.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 10.586V7z"
                                clip-rule="evenodd" transform="rotate(180 10 10)" />
                        @endif
                    </svg>
                </div>
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 text-center sm:text-left">{{ $title }}</h1>
            </div>
            <a href="{{ $backRoute }}"
                class="inline-flex items-center justify-center gap-2 w-full sm:w-auto px-5 py-2.5 text-white bg-gray-700 hover:bg-gray-800 rounded-lg text-sm transition-colors">
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
            <div class="p-6 sm:p-8">
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
                                            data-standard-pack="{{ $part->standard_pack_quantity }}"
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
                            class="w-full px-6 py-4 text-white {{ $accentBtn }} rounded-lg font-medium text-lg transition-colors flex items-center justify-center">
                            <span id="submitText">Registrar</span>
                            <div id="submitSpinner" class="hidden ml-2">
                                <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-white"></div>
                            </div>
                        </button>
                    </div>

                    @include('production-records.partials.flash-messages')
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

    <!-- Modal de advertencia: cantidad inusualmente alta -->
    <div id="highQuantityModal" class="fixed inset-0 bg-black bg-opacity-60 hidden items-center justify-center z-50 p-4">
        <div class="bg-white rounded-xl shadow-2xl max-w-md w-full overflow-hidden">
            <div class="p-8 text-center">
                <div class="mx-auto mb-4 w-16 h-16 rounded-full bg-amber-100 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-9 h-9 text-amber-600" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                </div>

                <h2 class="text-xl font-bold text-gray-900 mb-2">Cantidad Inusualmente Alta</h2>
                <p class="text-sm text-gray-500 mb-5">
                    Verifica que la cantidad capturada sea correcta antes de continuar.
                </p>

                <div class="text-5xl font-extrabold text-amber-600 mb-3" id="highQuantityValue">0</div>

                <p class="text-sm text-gray-600 mb-8">
                    Esta cantidad supera <span class="font-semibold">5 veces</span> el standard pack
                    (<span id="highQuantityStandard" class="font-semibold">0</span> pzas) del número de parte
                    seleccionado.
                </p>

                <div class="flex gap-3">
                    <button type="button" id="highQuantityCancel"
                        class="flex-1 px-4 py-3 rounded-lg font-medium border-2 border-gray-300 text-gray-700 hover:bg-gray-50 transition-colors">
                        Cancelar
                    </button>
                    <button type="button" id="highQuantityConfirm"
                        class="flex-1 px-4 py-3 rounded-lg font-medium bg-amber-600 text-white hover:bg-amber-700 transition-colors">
                        Sí, registrar
                    </button>
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
            const partNumberSelect = document.getElementById('partNumber');
            const quantityInput = document.getElementById('quantity');

            // Elementos del modal de advertencia por cantidad alta
            const highQuantityModal = document.getElementById('highQuantityModal');
            const highQuantityValue = document.getElementById('highQuantityValue');
            const highQuantityStandard = document.getElementById('highQuantityStandard');
            const highQuantityCancel = document.getElementById('highQuantityCancel');
            const highQuantityConfirm = document.getElementById('highQuantityConfirm');
            const HIGH_QUANTITY_MULTIPLIER = 5;
            let bypassHighQuantityCheck = false;

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

            // Standard pack del número de parte actualmente seleccionado
            function getSelectedStandardPack() {
                const option = partNumberSelect.options[partNumberSelect.selectedIndex];
                const value = option ? parseInt(option.getAttribute('data-standard-pack'), 10) : NaN;
                return Number.isFinite(value) && value > 0 ? value : null;
            }

            function openHighQuantityModal(quantity, standardPack) {
                highQuantityValue.textContent = quantity.toLocaleString('es-MX');
                highQuantityStandard.textContent = standardPack.toLocaleString('es-MX');
                highQuantityModal.classList.remove('hidden');
                highQuantityModal.classList.add('flex');
            }

            function closeHighQuantityModal() {
                highQuantityModal.classList.add('hidden');
                highQuantityModal.classList.remove('flex');
            }

            highQuantityCancel.addEventListener('click', function() {
                closeHighQuantityModal();
                quantityInput.focus();
                quantityInput.select();
            });

            highQuantityConfirm.addEventListener('click', function() {
                bypassHighQuantityCheck = true;
                closeHighQuantityModal();
                submitBtn.click();
            });

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
                    submitBtn.click();
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
            $('#scanForm').on('submit', function(e) {
                const quantity = parseInt(quantityInput.value, 10);
                const standardPack = getSelectedStandardPack();

                if (!bypassHighQuantityCheck && standardPack && Number.isFinite(quantity) &&
                    quantity > standardPack * HIGH_QUANTITY_MULTIPLIER) {
                    e.preventDefault();
                    openHighQuantityModal(quantity, standardPack);
                    return false;
                }

                bypassHighQuantityCheck = false;
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
