<div>
    <div class="w-full h-screen flex flex-col bg-gray-100" x-data="productionRecord">
        <!-- Header compacto -->

        <div class="px-6 py-4 mx-4 mt-4 bg-white border border-gray-200 rounded-lg">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    @if($realTime)
                        <!-- Punto verde -->
                        <span class="flex items-center space-x-1">
                            <span class="inline-block w-4 h-4 bg-green-500 rounded-full"></span>
                        </span>
                    @endif
                    <h1 class="text-xl font-bold text-gray-900">Registro de Producción</h1>
                </div>
                @if($shift)
                    <span class="px-3 py-1 text-sm font-semibold bg-blue-100 text-blue-800 rounded-full">
                        {{ $shift->name }} ({{ $shift->start_time }} - {{ $shift->end_time }})
                    </span>
                @endif
            </div>
        </div>

        <!-- Contenedor de tabla (relative para el sticky) -->
        <div class="flex-1 relative overflow-auto m-4 bg-white rounded-lg border border-gray-200">
            @if(count($records) > 0)
                <table class="min-w-full border-collapse rounded-lg">
                    <thead class="bg-gray-50">
                        <tr>
                            <!-- Columnas fijas -->
                           <th class="sticky top-0 px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider bg-gray-100 border z-20">
                                Estación
                            </th>
                            <th class="sticky top-0 px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider bg-gray-100 border z-20">
                                Núm. Parte
                            </th>
                            <th class="sticky top-0 px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider bg-gray-100 border z-20">
                                Paq. Estándar
                            </th>
                            <th class="sticky top-0 px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider bg-gray-100 border z-20">
                                Cant. Paquete
                            </th>
                            <th class="sticky top-0 px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider bg-gray-100 border z-20">
                                Modelo
                            </th>
                            <th class="sticky top-0 px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider bg-gray-100 border z-20">
                                Cant. Planeada
                            </th>

                            <!-- Celda vacía para alinear con las horas -->
                            <th class="sticky top-0 px-4 py-3 text-xs font-medium text-gray-600 uppercase tracking-wider bg-gray-100 border z-20"></th>

                            <!-- Columnas dinámicas de hora -->
                            @foreach($timeHeaders as $header)
                                <th class="sticky top-0 px-4 py-3 text-center text-xs font-medium text-gray-600 uppercase tracking-wider bg-gray-100 border z-20">
                                    {{ $header }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="bg-white">
                        @foreach($records as $record)
                            <!-- Fila de Inicio -->
                            <tr class="hover:bg-gray-50">
                                <!-- Columnas fijas (rowspan=2) -->
                                <td rowspan="2" class="px-6 py-2 whitespace-nowrap text-xs font-medium text-gray-600 border">
                                    {{ $record['work_center'] }}
                                </td>
                                <td rowspan="2" class="px-6 py-2 whitespace-nowrap text-xs font-medium text-gray-600 border">
                                    {{ $record['part_number'] }}
                                </td>
                                <td rowspan="2" class="px-6 py-2 whitespace-nowrap text-xs text-gray-600 border text-center">
                                    {{ $record['standard_pack'] }}
                                </td>
                                <td rowspan="2" class="px-6 py-2 whitespace-nowrap text-xs text-gray-600 border text-center">
                                    {{ $record['standard_pack_quantity'] }}
                                </td>
                                <td rowspan="2" class="px-6 py-2 whitespace-nowrap text-xs text-gray-600 border text-center">
                                    {{ $record['model'] }}
                                </td>
                                <td rowspan="2" class="px-6 py-2 whitespace-nowrap text-xs font-medium text-gray-900 border text-center">
                                    {{ $record['planned_quantity'] }}
                                </td>

                                <!-- Celda de inicio -->
                                <td class="px-4 py-2 whitespace-nowrap text-xs text-center font-medium bg-blue-50 text-blue-800 border">
                                    Entrada
                                </td>

                                <!-- Cantidades de inicio por hora -->
                                @foreach($timeHeaders as $header)
                                    <td class="px-4 py-2 whitespace-nowrap text-sm text-center text-gray-900 border">
                                        @if($record['entries'][$header] !== null)
                                            {{ number_format($record['entries'][$header]) }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                @endforeach
                            </tr>

                            <!-- Fila de Fin -->
                            <tr class="hover:bg-gray-50">
                                <!-- Celda de fin -->
                                <td class="px-4 py-2 whitespace-nowrap text-xs text-center font-medium bg-green-50 text-green-800 border">
                                    Salida
                                </td>

                                <!-- Cantidades de fin por hora -->
                                @foreach($timeHeaders as $header)
                                    <td class="px-4 py-2 whitespace-nowrap text-sm text-center text-gray-900 border">
                                        @if($record['exits'][$header] !== null)
                                            {{ number_format($record['exits'][$header]) }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <!-- Estado vacío -->
                <div class="flex-1 flex items-center justify-center">
                    <div class="text-center py-12">
                        <div class="w-24 h-24 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
                            <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 01-2-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">No hay planes de producción</h3>
                        <p class="text-gray-500">No se encontraron planes para el turno actual.</p>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @script
        <script>
            Alpine.data('productionRecord', () => {
                return {
                    init() {
                        if (@json($realTime)) {
                            setInterval(() => {
                                @this.dispatchSelf('refresh-production-records');
                            }, 5000); // Actualizar cada 5 segundos
                        }
                    }
                }
            });
        </script>
    @endscript
</div>
