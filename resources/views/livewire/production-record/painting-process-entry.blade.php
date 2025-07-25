<div>
    <!-- Header -->
    <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Registros de Producción</h1>
        <p class="text-gray-600">Últimos 15 registros ordenados por fecha</p>
    </div>

    <!-- Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6">
        @forelse($productionEntry as $index => $record)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-shadow duration-200">
                <!-- Card Header -->
                <div class="p-6 text-center">
                    <!-- Número de secuencia -->
                    <div class="inline-flex items-center justify-center w-12 h-12 bg-blue-100 text-blue-600 rounded-full text-lg font-bold mb-4">
                        {{ $index + 1 }}
                    </div>

                    <!-- Número de Parte -->
                    <div class="mb-4">
                        <h3 class="text-sm text-gray-500 uppercase tracking-wide mb-1">Número de Parte</h3>
                        <p class="text-xl font-semibold text-gray-900 break-all">
                            {{ $record->partNumber->number ?? 'N/A' }}
                        </p>
                    </div>

                    <!-- Cantidad -->
                    <div class="mb-4">
                        <h4 class="text-sm text-gray-500 uppercase tracking-wide mb-1">Cantidad</h4>
                        <div class="inline-flex items-center justify-center px-4 py-2 bg-green-50 text-green-700 rounded-lg">
                            <span class="text-2xl font-bold">{{ number_format($record->quantity) }}</span>
                            <span class="text-sm ml-1">pzs</span>
                        </div>
                    </div>

                    <!-- Indicador de estado (opcional, muy sutil) -->
                    <div class="flex justify-center">
                        <div class="w-2 h-2 bg-green-400 rounded-full"></div>
                    </div>
                </div>
            </div>
        @empty
            <!-- Estado vacío -->
            <div class="col-span-full">
                <div class="text-center py-12">
                    <div class="w-24 h-24 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
                        <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No hay registros</h3>
                    <p class="text-gray-500">No se encontraron registros de producción.</p>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Indicador de actualización en tiempo real -->
    <div class="mt-8 text-center">
        <div class="inline-flex items-center px-3 py-1 text-sm text-gray-500 bg-white rounded-full shadow-sm border border-gray-200">
            <div class="w-2 h-2 bg-green-400 rounded-full mr-2 animate-pulse"></div>
            Actualizándose en tiempo real
        </div>
    </div>
</div>
