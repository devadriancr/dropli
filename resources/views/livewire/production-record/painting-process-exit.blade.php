<div x-data="exitTable">
    <!-- Header -->
    <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Salida de Material</h1>
    </div>

    <!-- Cards Stack -->
    <div class="max-w-2xl mx-auto space-y-4">
        @forelse($recentExitRecords as $index => $record)
            <div
                class="bg-white rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow duration-200">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <!-- Información principal centrada -->
                        <div class="flex-1">
                            <div class="flex items-center justify-between">
                                <!-- Número de Parte -->
                                <div class="flex-1 pr-4">
                                    <h3 class="text-xs text-gray-500 uppercase tracking-wide mb-1">Número de Parte</h3>
                                    <p class="text-sm font-semibold text-gray-900 leading-tight break-words">
                                        {{ $record->productionPlan->partNumber->number }}
                                    </p>
                                </div>

                                <!-- Secuencia -->
                                {{-- <div class="flex-1 text-center px-2">
                                    <h4 class="text-xs text-gray-500 uppercase tracking-wide mb-1">Secuencia</h4>
                                    <div
                                        class="inline-flex items-center px-2 py-1 bg-green-50 text-green-700 rounded-lg">
                                        <span
                                            class="text-lg font-bold">{{ $record->productionPlan->partNumber->standard_pack_quantity }}</span>
                                    </div>
                                </div> --}}

                                <!-- Cantidad -->
                                <div class="flex-1 text-center px-2">
                                    <h4 class="text-xs text-gray-500 uppercase tracking-wide mb-1">Cantidad</h4>
                                    <div
                                        class="inline-flex items-center px-2 py-1 bg-green-50 text-green-700 rounded-lg">
                                        <span class="text-lg font-bold">{{ number_format($record->productionPlan->partNumber->standard_pack_quantity) }}</span>
                                        <span class="text-xs ml-1">pzs</span>
                                    </div>
                                </div>

                                <!-- Fecha y hora con ícono -->
                                <div class="flex-1 flex items-center justify-end space-x-3 pl-4">
                                    <div class="text-right">
                                        <div class="text-xs text-gray-500 mb-1">
                                            {{ $record->created_at->format('d/m/Y') }}
                                        </div>
                                        <div class="text-xs text-gray-600 font-medium">
                                            {{ $record->created_at->format('H:i') }}
                                        </div>
                                    </div>
                                    <!-- Ícono de flecha hacia abajo (salida) -->
                                    <div class="flex-shrink-0">
                                        <div
                                            class="inline-flex items-center justify-center w-8 h-8 bg-green-100 text-green-600 rounded-full">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M17 13l-5 5m0 0l-5-5m5 5V6" />
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <!-- Estado vacío -->
            <div class="max-w-2xl mx-auto">
                <div class="text-center py-12">
                    <div class="w-24 h-24 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
                        <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                            </path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No hay registros</h3>
                    <p class="text-gray-500">No se encontraron registros de salida de material.</p>
                </div>
            </div>
        @endforelse
    </div>
</div>
