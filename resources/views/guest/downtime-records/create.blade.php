<x-guest-layout>
    <div class="bg-gray-100 dark:bg-gray-900 min-h-screen py-8">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="w-full max-w-4xl">
                <!-- Success Message -->
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

                <!-- Error Message -->
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

                <!-- Form Card -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-8">
                    <!-- Header dentro del card y centrado -->
                    <div class="text-center mb-6">
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Registro de Paro</h1>
                        <p class="text-gray-600 dark:text-gray-400 mt-2">Registra los paros de línea</p>
                    </div>

                    <form action="{{ route('guest.downtime-records.store') }}" method="POST" class="space-y-6">
                        @csrf

                        <!-- Work Center y Downtime Reason en una fila -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Work Center -->
                            <div>
                                <label for="work_center_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Centro de Trabajo *
                                </label>
                                <select name="work_center_id" id="work_center_id"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white @error('work_center_id') border-red-500 @enderror"
                                        required>
                                    <option value="">Seleccione centro de trabajo</option>
                                    @foreach($workCenters as $workCenter)
                                        <option value="{{ $workCenter->id }}" {{ old('work_center_id') == $workCenter->id ? 'selected' : '' }}>
                                            {{ $workCenter->number }} - {{ $workCenter->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('work_center_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Downtime Reason -->
                            <div>
                                <label for="downtime_reason_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Razón de Paro *
                                </label>
                                <select name="downtime_reason_id" id="downtime_reason_id"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white @error('downtime_reason_id') border-red-500 @enderror"
                                        required>
                                    <option value="">Seleccione una razón</option>
                                    @foreach($downtimeReasons as $reason)
                                        <option value="{{ $reason->id }}" {{ old('downtime_reason_id') == $reason->id ? 'selected' : '' }}>
                                            {{ $reason->code }} - {{ $reason->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('downtime_reason_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Tiempos en una fila -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <!-- Start Time -->
                            <div>
                                <label for="start_time" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Hora de Inicio *
                                </label>
                                <input type="datetime-local" name="start_time" id="start_time"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white @error('start_time') border-red-500 @enderror"
                                       value="{{ old('start_time') }}" required>
                                @error('start_time')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- End Time -->
                            <div>
                                <label for="end_time" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Hora de Fin *
                                </label>
                                <input type="datetime-local" name="end_time" id="end_time"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white @error('end_time') border-red-500 @enderror"
                                       value="{{ old('end_time') }}" required>
                                @error('end_time')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Minutes (calculated) -->
                            <div>
                                <label for="minutes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Minutos *
                                </label>
                                <input type="number" name="minutes" id="minutes" step="0.01" min="0"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-100 dark:bg-gray-600 text-gray-500 dark:text-gray-300 @error('minutes') border-red-500 @enderror"
                                       value="{{ old('minutes') }}" required readonly>
                                <p class="mt-1 text-xs text-gray-500">Calculado automáticamente</p>
                                @error('minutes')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="pt-4">
                            <button type="submit"
                                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-6 rounded-lg transition duration-200 ease-in-out">
                                <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Guardar Registro
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- CSS y JS para Select2 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-rc.0/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-rc.0/js/select2.min.js"></script>

    <style>
        /* Estilos Select2 igual que el scrap */
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

        /* Modo oscuro */
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

    <script>
        $(document).ready(function() {
            // Inicializar Select2
            $('#work_center_id, #downtime_reason_id').select2({
                placeholder: 'Seleccione una opción...',
                allowClear: true,
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

            // Focus automático
            setTimeout(function() {
                $('#work_center_id').select2('focus');
            }, 100);

            // Manejar errores de validación
            @if($errors->has('work_center_id'))
                $('#work_center_id').next('.select2-container').find('.select2-selection').css('border-color', '#ef4444');
            @endif

            @if($errors->has('downtime_reason_id'))
                $('#downtime_reason_id').next('.select2-container').find('.select2-selection').css('border-color', '#ef4444');
            @endif

            // Calcular minutos inicial si hay valores
            if ($('#start_time').val() && $('#end_time').val()) {
                calculateMinutes();
            }
        });
    </script>
</x-guest-layout>
