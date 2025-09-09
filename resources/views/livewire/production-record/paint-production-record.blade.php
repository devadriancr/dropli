<div>
    <div class="w-full h-screen flex flex-col bg-gray-100" x-data="productionRecord">
        <!-- Header compacto (modificado: botón Login/Dashboard ahora junto al turno) -->
        <div class="px-6 py-4 mx-4 mt-4 bg-white border border-gray-200 rounded-lg">
            <div class="flex items-center justify-between">
                <!-- Izquierda: título y punto realTime -->
                <div class="flex items-center space-x-2">
                    @if($realTime)
                        <!-- Punto verde -->
                        <span class="flex items-center space-x-1">
                            <span class="inline-block w-4 h-4 bg-green-500 rounded-full"></span>
                        </span>
                    @endif
                    <h1 class="text-xl font-bold text-gray-900">Registro de Producción</h1>
                </div>

                <div class="flex items-center space-x-2">
                    <!-- Fecha y Turno -->
                    @if($shift)
                        <span
                            class="px-3 py-1 text-sm font-semibold bg-blue-50 text-blue-800 rounded-full whitespace-nowrap">
                            {{ $date }}
                        </span>
                        <span
                            class="px-3 py-1 text-sm font-semibold bg-blue-50 text-blue-800 rounded-full whitespace-nowrap">
                            {{ $shift->name }}
                        </span>
                        <span
                            class="px-3 py-1 text-sm font-semibold bg-blue-50 text-blue-800 rounded-full whitespace-nowrap">
                            {{ $shift->start_time }} - {{ $shift->end_time }}
                        </span>
                    @endif

                    <!-- Botón de Paro de Línea -->
                    <a href="" class="inline-flex items-center px-4 py-1.5 text-sm font-medium text-[#1b1b18] border border-[#19140035] hover:border-[#1915014a] rounded-md">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Paro de Línea
                    </a>
                </div>
            </div>
        </div>

        <!-- Contenedor de tabla (relative para el sticky) -->
        <div class="flex-1 relative overflow-auto m-4 bg-white rounded-lg border border-gray-200">
            @if(count($records) > 0)
                <table class="min-w-full border-collapse rounded-lg">
                    <thead class="bg-gray-100">
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

                        <th class="sticky top-0 px-4 py-3 text-center text-xs font-medium text-gray-600 uppercase tracking-wider bg-gray-100 border z-20">

                        </th>
                    </tr>
                    </thead>
                    <tbody class="bg-white">
                    @foreach($records as $record)
                        <!-- Fila de Entrada -->
                        <tr class="hover:bg-gray-50">
                            <!-- Columnas fijas -->
                            <td rowspan="2"
                                class="px-6 py-2 whitespace-nowrap text-xs font-medium text-gray-900 border">
                                {{ $record['line_name'] }}
                            </td>
                            <td rowspan="2"
                                class="px-6 py-2 whitespace-nowrap text-xs font-medium text-gray-600 border">
                                {{ $record['part_number'] }}
                            </td>
                            <td rowspan="2"
                                class="px-6 py-2 whitespace-nowrap text-xs text-gray-600 border text-center">
                                {{ $record['standard_pack'] }} - {{ $record['standard_pack_quantity'] }}
                            </td>
                            <td rowspan="2"
                                class="px-6 py-2 whitespace-nowrap text-xs text-gray-600 border text-center">
                                @if($record['model'] != '' || $record['model'] != null)
                                    {{ $record['model'] }}
                                @else
                                    -
                                @endif
                            </td>
                            <td rowspan="2"
                                class="px-6 py-2 whitespace-nowrap text-xs font-medium text-gray-600 border text-center">
                                {{ $record['planned_quantity'] }}
                            </td>

                            <!-- Celda de entrada -->
                            <td class="px-4 py-2 whitespace-nowrap text-xs text-center font-medium bg-blue-50 text-blue-800 border">
                                Entrada
                            </td>

                            @foreach($timeHeaders as $header)
                                <td class="px-4 py-2 whitespace-nowrap text-sm text-center text-gray-900 border">
                                    @if($record['entries'][$header] !== null)
                                        {{ number_format($record['entries'][$header]) }}
                                    @else
                                        -
                                    @endif
                                </td>
                            @endforeach

                            <td rowspan="2" class="px-4 py-2 whitespace-nowrap text-sm text-center border">
                                <a class="inline-flex items-center p-2 text-red-600 border border-red-500 rounded-md hover:border-red-600 hover:bg-red-50 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </a>
                            </td>
                        </tr>

                        <!-- Fila de Salida -->
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 whitespace-nowrap text-xs text-center font-medium bg-green-50 text-green-800 border">
                                Salida
                            </td>

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
                <div class="flex-1 flex items-center justify-center py-12">
                    <div class="text-center">
                        <div class="w-24 h-24 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
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
                        @this.dispatchSelf('refresh-production-records')
                            ;
                        }, 5000); // Actualizar cada 5 segundos
                    }
                }
            }
        });
    </script>
    @endscript
</div>
