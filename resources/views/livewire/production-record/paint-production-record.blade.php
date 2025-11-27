<div>
    <div class="w-full h-screen flex flex-col bg-gray-100" x-data="productionRecord">
        <!-- Header compacto (modificado: menú de opciones agregado) -->
        <div class="px-6 py-4 mx-4 mt-4 bg-white border border-gray-200 rounded-lg">
            <div class="flex items-center justify-between">
                <!-- Izquierda: título y punto realTime -->
                <div class="flex items-center space-x-2">
                    @if ($realTime)
                        <!-- Punto verde -->
                        <span class="flex items-center space-x-1">
                            <span class="inline-block w-4 h-4 bg-green-500 rounded-full"></span>
                        </span>
                    @endif
                    <h1 class="text-xl font-bold text-gray-900">Registro de Producción</h1>
                </div>

                <div class="flex items-center space-x-2">
                    <!-- Fecha y Turno (solo horas y minutos) -->
                    @if ($shift)
                        <span
                            class="px-3 py-1 text-sm font-semibold bg-blue-50 text-blue-800 rounded-full whitespace-nowrap">
                            {{ \Carbon\Carbon::parse($date)->format('d-m-Y') }}
                        </span>
                        <span
                            class="px-3 py-1 text-sm font-semibold bg-blue-50 text-blue-800 rounded-full whitespace-nowrap">
                            {{ $shift->name }}
                        </span>
                        <span
                            class="px-3 py-1 text-sm font-semibold bg-blue-50 text-blue-800 rounded-full whitespace-nowrap">
                            {{ \Carbon\Carbon::parse($shift->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($shift->end_time)->format('H:i') }}
                        </span>
                    @endif

                    <!-- Menú de opciones (tres puntos) -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" @click.away="open = false"
                            class="p-2 hover:bg-gray-100 rounded-full transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600" viewBox="0 0 20 20"
                                fill="currentColor">
                                <path
                                    d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
                            </svg>
                        </button>

                        <!-- Dropdown menu -->
                        <div x-show="open" x-transition:enter="transition ease-out duration-100"
                            x-transition:enter-start="transform opacity-0 scale-95"
                            x-transition:enter-end="transform opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="transform opacity-100 scale-100"
                            x-transition:leave-end="transform opacity-0 scale-95"
                            class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-50"
                            style="display: none;">

                            <!-- Opción: Entrada -->
                            <a href="{{ route('production-records.entry-scan') }}"
                                class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-800 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3 text-blue-600"
                                    viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v3.586L7.707 9.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 10.586V7z"
                                        clip-rule="evenodd" />
                                </svg>
                                <span>Entrada</span>
                            </a>

                            <!-- Opción: Salida -->
                            <a href="{{ route('production-records.exit-scan') }}"
                                class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-green-50 hover:text-green-800 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3 text-green-600"
                                    viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v3.586L7.707 9.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 10.586V7z"
                                        clip-rule="evenodd" transform="rotate(180 10 10)" />
                                </svg>
                                <span>Salida</span>
                            </a>

                            <!-- Separador -->
                            <div class="border-t border-gray-100 my-1"></div>

                            <!-- Opción: Login/Dashboard -->
                            @auth
                                <a href="{{ url('/home') }}"
                                    class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3 text-gray-600"
                                        viewBox="0 0 20 20" fill="currentColor">
                                        <path
                                            d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" />
                                    </svg>
                                    <span>Panel Administrativo</span>
                                </a>
                            @else
                                <a href="{{ route('login') }}"
                                    class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3 text-gray-600"
                                        viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M3 3a1 1 0 011 1v12a1 1 0 11-2 0V4a1 1 0 011-1zm7.707 3.293a1 1 0 010 1.414L9.414 9H17a1 1 0 110 2H9.414l1.293 1.293a1 1 0 01-1.414 1.414l-3-3a1 1 0 010-1.414l3-3a1 1 0 011.414 0z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <span>Iniciar Sesión</span>
                                </a>
                            @endauth
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contenedor de tabla (relative para el sticky) -->
        <div class="flex-1 relative overflow-auto m-4 bg-white rounded-lg border border-gray-200">
            @if (count($records) > 0)
                <table class="min-w-full border-collapse rounded-lg">
                    <thead class="bg-gray-100">
                        <tr>
                            <!-- Columnas fijas -->
                            <th
                                class="sticky top-0 px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider bg-gray-100 border z-20">
                                Estación
                            </th>
                            <th
                                class="sticky top-0 px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider bg-gray-100 border z-20">
                                Núm. Parte
                            </th>
                            <th
                                class="sticky top-0 px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider bg-gray-100 border z-20">
                                Paq. Estándar
                            </th>
                            <th
                                class="sticky top-0 px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider bg-gray-100 border z-20">
                                Modelo
                            </th>
                            <th
                                class="sticky top-0 px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider bg-gray-100 border z-20">
                                Cant. Plan
                            </th>
                            <!-- Nueva columna: Cant. Real -->
                            <th
                                class="sticky top-0 px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider bg-gray-100 border z-20">
                                Cant. Real
                            </th>

                            <!-- Celda vacía para alinear con las horas -->
                            <th
                                class="sticky top-0 px-4 py-3 text-xs font-medium text-gray-600 uppercase tracking-wider bg-gray-100 border z-20">
                            </th>

                            <!-- Columnas dinámicas de hora -->
                            @foreach ($timeHeaders as $header)
                                <th
                                    class="sticky top-0 px-4 py-3 text-center text-xs font-medium text-gray-600 uppercase tracking-wider bg-gray-100 border z-20">
                                    {{ $header }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="bg-white">
                        @foreach ($records as $record)
                            <!-- Fila de Entrada -->
                            <tr class="hover:bg-gray-50">
                                <!-- Columnas fijas -->
                                <td rowspan="2"
                                    class="px-6 py-2 whitespace-nowrap uppercase text-xs font-medium text-gray-900 border">
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
                                    @if ($record['model'] != '' || $record['model'] != null)
                                        {{ $record['model'] }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td rowspan="2"
                                    class="px-6 py-2 whitespace-nowrap text-xs font-medium text-gray-600 border text-center">
                                    {{ $record['planned_quantity'] }}
                                </td>
                                <!-- Nueva celda para Cant. Real -->
                                <td rowspan="2"
                                    class="px-6 py-2 whitespace-nowrap text-xs font-medium text-gray-600 border text-center">
                                    {{ number_format($record['total_exits']) }}
                                </td>

                                <!-- Celda de entrada -->
                                <td
                                    class="px-4 py-2 whitespace-nowrap text-xs text-center font-medium text-blue-800 border">
                                    Entrada
                                </td>

                                @foreach ($timeHeaders as $header)
                                    @php $state = $columnsState[$header] ?? 'future'; @endphp
                                    <td
                                        class="px-4 py-2 whitespace-nowrap text-sm text-center border
                                        @if ($state === 'yellow') bg-yellow-50 text-yellow-800 @elseif($state === 'green') bg-green-50 text-green-800 @endif">
                                        @if ($record['entries'][$header] !== null && $record['entries'][$header] !== 0)
                                            {{ number_format($record['entries'][$header]) }}
                                        @elseif($record['entries'][$header] === 0)
                                            0
                                        @else
                                            -
                                        @endif
                                    </td>
                                @endforeach
                            </tr>

                            <!-- Fila de Salida -->
                            <tr class="hover:bg-gray-50">
                                <td
                                    class="px-4 py-2 whitespace-nowrap text-xs text-center font-medium text-green-800 border">
                                    Salida
                                </td>

                                @foreach ($timeHeaders as $header)
                                    @php $state = $columnsState[$header] ?? 'future'; @endphp
                                    <td
                                        class="px-4 py-2 whitespace-nowrap text-sm text-center border
                                        @if ($state === 'yellow') bg-yellow-50 text-yellow-800 @elseif($state === 'green') bg-green-50 text-green-800 @endif">
                                        @if ($record['exits'][$header] !== null && $record['exits'][$header] !== 0)
                                            {{ number_format($record['exits'][$header]) }}
                                        @elseif($record['exits'][$header] === 0)
                                            0
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
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-gray-400" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
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
