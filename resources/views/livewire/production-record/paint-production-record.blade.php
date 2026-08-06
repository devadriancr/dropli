<div>
    <div class="w-full h-screen flex flex-col bg-gray-100" x-data="productionRecord">
        <!-- Header compacto (modificado: menú de opciones agregado) -->
        <div class="px-6 py-4 mx-4 mt-4 bg-white border border-gray-200 rounded-lg shadow-sm">
            <div class="flex items-center justify-between flex-wrap gap-3">
                <!-- Izquierda: título y punto realTime -->
                <div class="flex items-center space-x-3">
                    <h1 class="text-xl font-bold text-gray-900">Registro de Producción</h1>
                    @if ($realTime)
                        <span
                            class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold text-green-700 bg-green-50 border border-green-100 rounded-full">
                            <span class="relative flex h-2 w-2">
                                <span
                                    class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                            </span>
                            En vivo
                        </span>
                    @endif
                </div>

                <div class="flex items-center gap-2 flex-wrap">
                    <!-- Selector de fecha (calendario personalizado) -->
                    <div class="relative" x-data="datePicker(@js($date))">
                        <button type="button" @click="open = !open" @click.away="open = false"
                            class="flex items-center gap-2 px-3 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-sm font-semibold text-gray-800 hover:bg-gray-100 hover:border-gray-300 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span x-text="label" class="capitalize"></span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-gray-400" viewBox="0 0 20 20"
                                fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.25a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z"
                                    clip-rule="evenodd" />
                            </svg>
                        </button>

                        <!-- Panel del calendario -->
                        <div x-show="open" x-transition:enter="transition ease-out duration-100"
                            x-transition:enter-start="transform opacity-0 scale-95"
                            x-transition:enter-end="transform opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="transform opacity-100 scale-100"
                            x-transition:leave-end="transform opacity-0 scale-95"
                            class="absolute right-0 mt-2 w-72 bg-white rounded-lg shadow-lg border border-gray-200 p-3 z-50"
                            style="display: none;">

                            <!-- Navegación de mes -->
                            <div class="flex items-center justify-between mb-2">
                                <button type="button" @click="prevMonth"
                                    class="p-1.5 text-gray-500 hover:bg-gray-100 rounded-md transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20"
                                        fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </button>
                                <span class="text-sm font-semibold text-gray-800 capitalize" x-text="monthLabel"></span>
                                <button type="button" @click="nextMonth"
                                    class="p-1.5 text-gray-500 hover:bg-gray-100 rounded-md transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20"
                                        fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>

                            <!-- Encabezado de días -->
                            <div class="grid grid-cols-7 text-center text-[11px] font-semibold text-gray-400 mb-1">
                                <template x-for="(d, i) in ['D', 'L', 'M', 'M', 'J', 'V', 'S']" :key="i">
                                    <span x-text="d"></span>
                                </template>
                            </div>

                            <!-- Días del mes -->
                            <div class="grid grid-cols-7 gap-y-1 place-items-center">
                                <template x-for="cell in days" :key="cell.key">
                                    <button type="button" @click="cell.dateStr && selectDay(cell)"
                                        x-text="cell.day ?? ''"
                                        :disabled="!cell.dateStr"
                                        :class="{
                                            'invisible': !cell.day,
                                            'bg-blue-600 text-white hover:bg-blue-600': cell.isSelected,
                                            'text-gray-700 hover:bg-blue-50': !cell.isSelected && !cell.isToday,
                                            'border border-blue-400 text-blue-700 hover:bg-blue-50': cell.isToday && !cell.isSelected,
                                        }"
                                        class="h-8 w-8 flex items-center justify-center text-xs font-medium rounded-full transition-colors">
                                    </button>
                                </template>
                            </div>

                            <div class="mt-2 pt-2 border-t border-gray-100 flex justify-end">
                                <button type="button" @click="selectToday"
                                    class="text-xs font-semibold text-blue-600 hover:text-blue-800 transition-colors">
                                    Hoy
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Selector de turno (personalizado) -->
                    <div class="relative"
                        x-data="shiftPicker(@js($shifts->map(fn($s) => [
                            'id' => $s->id,
                            'name' => $s->name,
                            'range' => \Carbon\Carbon::parse($s->start_time)->format('H:i') . ' - ' . \Carbon\Carbon::parse($s->end_time)->format('H:i'),
                        ])->values()), @js($shiftId))">
                        <button type="button" @click="open = !open" @click.away="open = false"
                            class="flex items-center gap-2 px-3 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-sm font-semibold text-gray-800 hover:bg-gray-100 hover:border-gray-300 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span x-text="selectedLabel"></span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-gray-400" viewBox="0 0 20 20"
                                fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.25a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z"
                                    clip-rule="evenodd" />
                            </svg>
                        </button>

                        <!-- Panel de turnos -->
                        <div x-show="open" x-transition:enter="transition ease-out duration-100"
                            x-transition:enter-start="transform opacity-0 scale-95"
                            x-transition:enter-end="transform opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="transform opacity-100 scale-100"
                            x-transition:leave-end="transform opacity-0 scale-95"
                            class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-50"
                            style="display: none;">
                            <template x-for="s in shifts" :key="s.id">
                                <button type="button" @click="select(s)"
                                    class="w-full flex items-center justify-between gap-3 px-3 py-2 hover:bg-blue-50 transition-colors"
                                    :class="{ 'bg-blue-50': s.id === selectedId }">
                                    <span class="flex items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-600"
                                            :class="s.id === selectedId ? 'opacity-100' : 'opacity-0'" viewBox="0 0 20 20"
                                            fill="currentColor">
                                            <path fill-rule="evenodd"
                                                d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        <span class="text-sm font-medium"
                                            :class="s.id === selectedId ? 'text-blue-700' : 'text-gray-700'"
                                            x-text="s.name"></span>
                                    </span>
                                    <span class="text-xs text-gray-400" x-text="s.range"></span>
                                </button>
                            </template>
                        </div>
                    </div>

                    <!-- Horario del turno seleccionado -->
                    @if ($shift)
                        <span
                            class="px-3 py-1.5 text-sm font-semibold bg-blue-50 text-blue-700 border border-blue-100 rounded-full whitespace-nowrap">
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
        <div class="flex-1 relative overflow-auto m-4 bg-white rounded-lg border border-gray-200 shadow-sm">
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
                            <!-- Cant. Real -->
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

                            <!-- Total -->
                            <th
                                class="sticky top-0 px-4 py-3 text-center text-xs font-medium text-gray-600 uppercase tracking-wider bg-gray-100 border z-20">
                                Total
                            </th>
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
                                <!-- Cant. Plan -->
                                <td rowspan="2"
                                    class="px-6 py-2 whitespace-nowrap text-xs font-medium text-gray-600 border text-center">
                                    {{ $record['planned_quantity'] }}
                                </td>
                                <!-- Cant. Real -->
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
                                        @else
                                            -
                                        @endif
                                    </td>
                                @endforeach

                                <!-- Nueva celda de Total para Entrada -->
                                <td
                                    class="px-4 py-2 whitespace-nowrap text-sm text-center border font-medium text-blue-800">
                                    {{ number_format($record['total_entries']) }}
                                </td>
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
                                        @else
                                            -
                                        @endif
                                    </td>
                                @endforeach

                                <!-- Nueva celda de Total para Salida -->
                                <td
                                    class="px-4 py-2 whitespace-nowrap text-sm text-center border font-medium text-green-800">
                                    {{ number_format($record['total_exits']) }}
                                </td>
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
                        <p class="text-gray-500">No se encontraron planes para la fecha y turno seleccionados.</p>
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
                            }, 10000); // Actualizar cada 10 segundos
                        }
                    }
                }
            });

            Alpine.data('datePicker', (initialDate) => {
                const parseDate = (value) => {
                    const [year, month, day] = value.split('-').map(Number);
                    return new Date(year, month - 1, day);
                };

                const toDateStr = (date) => {
                    const y = date.getFullYear();
                    const m = String(date.getMonth() + 1).padStart(2, '0');
                    const d = String(date.getDate()).padStart(2, '0');
                    return `${y}-${m}-${d}`;
                };

                const monthFormatter = new Intl.DateTimeFormat('es-MX', { month: 'long', year: 'numeric' });
                const labelFormatter = new Intl.DateTimeFormat('es-MX', { day: '2-digit', month: 'short', year: 'numeric' });

                return {
                    open: false,
                    selected: initialDate,
                    viewDate: parseDate(initialDate),

                    get label() {
                        return labelFormatter.format(parseDate(this.selected));
                    },

                    get monthLabel() {
                        return monthFormatter.format(this.viewDate);
                    },

                    get days() {
                        const year = this.viewDate.getFullYear();
                        const month = this.viewDate.getMonth();
                        const startOffset = new Date(year, month, 1).getDay();
                        const daysInMonth = new Date(year, month + 1, 0).getDate();
                        const todayStr = toDateStr(new Date());

                        const cells = [];
                        for (let i = 0; i < startOffset; i++) {
                            cells.push({ key: `empty-${i}`, day: null, dateStr: null });
                        }
                        for (let day = 1; day <= daysInMonth; day++) {
                            const dateStr = toDateStr(new Date(year, month, day));
                            cells.push({
                                key: dateStr,
                                day,
                                dateStr,
                                isToday: dateStr === todayStr,
                                isSelected: dateStr === this.selected,
                            });
                        }
                        return cells;
                    },

                    prevMonth() {
                        this.viewDate = new Date(this.viewDate.getFullYear(), this.viewDate.getMonth() - 1, 1);
                    },

                    nextMonth() {
                        this.viewDate = new Date(this.viewDate.getFullYear(), this.viewDate.getMonth() + 1, 1);
                    },

                    selectDay(cell) {
                        this.selected = cell.dateStr;
                        this.$wire.set('date', cell.dateStr);
                        this.open = false;
                    },

                    selectToday() {
                        const today = new Date();
                        this.viewDate = new Date(today.getFullYear(), today.getMonth(), 1);
                        this.selected = toDateStr(today);
                        this.$wire.set('date', this.selected);
                        this.open = false;
                    },
                }
            });

            Alpine.data('shiftPicker', (shifts, initialId) => {
                return {
                    open: false,
                    shifts,
                    selectedId: initialId,

                    get selectedLabel() {
                        const found = this.shifts.find((s) => s.id === this.selectedId);
                        return found ? found.name : 'Seleccionar turno';
                    },

                    select(s) {
                        this.selectedId = s.id;
                        this.$wire.set('shiftId', s.id);
                        this.open = false;
                    },
                }
            });
        </script>
    @endscript
</div>
