<div class="w-full h-screen flex flex-col bg-gray-200">
    <!-- Header compacto -->
    <div class="px-4 py-3 bg-white border-gray-200 rounded-lg">
        <h1 class="text-xl font-bold text-gray-900">Registro de Producción - Pintura</h1>
        @if($shift)
            <p class="text-sm text-gray-600">
                Turno: {{ $shift->name }} ({{ $shift->start_time }} - {{ $shift->end_time }})
            </p>
        @endif
    </div>

    <!-- Contenedor de tabla -->
    <div class="flex-1 overflow-auto bg-white rounded-lg border border-gray-200">
        @if(count($records) > 0)
            <table class="min-w-full border-collapse rounded-lg">
                <thead class="bg-gray-50 sticky top-0">
                    <tr>
                        <!-- Columnas fijas -->
                        <th rowspan="2" class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border sticky left-0 bg-gray-50 z-10">
                            Núm. Parte
                        </th>
                        <th rowspan="2" class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border">
                            Paq. Estándar
                        </th>
                        <th rowspan="2" class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border">
                            Cant. Paquete
                        </th>
                        <th rowspan="2" class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border">
                            Modelo
                        </th>

                        <!-- Celda vacía para alinear con las horas -->
                        <th class="px-2 py-2 text-xs font-medium text-gray-500 uppercase tracking-wider border"></th>

                        <!-- Columnas dinámicas de hora -->
                        @foreach($timeHeaders as $header)
                            <th class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border">
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
                            <td rowspan="2" class="px-4 py-2 whitespace-nowrap text-sm font-medium text-gray-900 border sticky left-0 bg-white z-10">
                                {{ $record['part_number'] }}
                            </td>
                            <td rowspan="2" class="px-4 py-2 whitespace-nowrap text-sm text-gray-500 border">
                                {{ $record['standard_pack'] }}
                            </td>
                            <td rowspan="2" class="px-4 py-2 whitespace-nowrap text-sm text-gray-500 border">
                                {{ $record['standard_pack_quantity'] }}
                            </td>
                            <td rowspan="2" class="px-4 py-2 whitespace-nowrap text-sm text-gray-500 border">
                                {{ $record['model'] }}
                            </td>

                            <!-- Celda de inicio -->
                            <td class="px-2 py-1 whitespace-nowrap text-xs text-center font-medium bg-blue-50 text-blue-800 border">
                                Inicio
                            </td>

                            <!-- Cantidades de inicio por hora -->
                            @foreach($timeHeaders as $header)
                                <td class="px-2 py-1 whitespace-nowrap text-sm text-center text-gray-900 border">
                                    @if($record['entries'][$header] !== null)
                                        {{ number_format($record['entries'][$header]) }}
                                    @endif
                                </td>
                            @endforeach
                        </tr>

                        <!-- Fila de Fin -->
                        <tr class="hover:bg-gray-50">
                            <!-- Celda de fin -->
                            <td class="px-2 py-1 whitespace-nowrap text-xs text-center font-medium bg-red-50 text-red-800 border">
                                Fin
                            </td>

                            <!-- Cantidades de fin por hora -->
                            @foreach($timeHeaders as $header)
                                <td class="px-2 py-1 whitespace-nowrap text-sm text-center text-gray-900 border">
                                    @if($record['exits'][$header] !== null)
                                        {{ number_format($record['exits'][$header]) }}
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No hay registros de producción</h3>
                    <p class="text-gray-500">No se encontraron registros para el turno actual.</p>
                </div>
            </div>
        @endif
    </div>
</div>
