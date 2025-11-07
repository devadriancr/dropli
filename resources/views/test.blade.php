<x-guest-layout>
    <div class="min-h-screen p-6 bg-gray-50">
        <div class="max-w-full mx-auto">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
                <h1 class="text-2xl font-semibold text-gray-900">Material Recibido</h1>
            </div>

            <!-- Tabla contenedor con altura fija y scroll interno -->
            <div class="bg-white rounded-xl shadow"
                style="height: calc(100vh - 200px); display: flex; flex-direction: column;">
                @php
                    // Obtener las fechas para los encabezados
                    $dates = collect($final)->pluck('date')->filter()->values()->all();

                    // Construir estructura de partes
                    $partsData = [];
                    foreach ($final as $day) {
                        $dateLabel = $day['date'] ?? null;
                        foreach ($day['items'] as $item) {
                            $part = $item['item_number'] ?? 'UNKNOWN';
                            if (!isset($partsData[$part])) {
                                $partsData[$part] = [
                                    'item_number' => $part,
                                    'item_class' => $item['item_class'] ?? null,
                                    'vendor_number' => $item['vendor_number'] ?? null,
                                    'vendor_name' => $item['vendor_name'] ?? null,
                                    'work_center_no' => $item['work_center_no'] ?? null,
                                    'work_center_name' => $item['work_center_name'] ?? null,
                                    'ordered_total' => 0,
                                    'received_total' => 0,
                                    'by_date' => [],
                                ];
                            }
                            // Sumar totales acumulados
                            $partsData[$part]['ordered_total'] += $item['ordered_qty'] ?? 0;
                            $partsData[$part]['received_total'] += $item['received_qty'] ?? 0;
                            // Guardar datos por fecha
                            $partsData[$part]['by_date'][$dateLabel] = $item;
                        }
                    }

                    // Totales por día
                    $totalsByDate = [];
                    foreach ($final as $day) {
                        $label = $day['date'] ?? null;
                        $totalsByDate[$label] = $day['percentage_total'] ?? null;
                    }
                @endphp

                <!-- Contenedor con scroll para el cuerpo de la tabla -->
                <div class="flex-1 overflow-auto">
                    <table class="min-w-full table-fixed">
                        <thead class="bg-gray-100 sticky top-0 z-20">
                            <tr>
                                <th
                                    class="sticky left-0 px-4 py-3 text-left font-medium text-gray-700 bg-gray-100 z-30 w-56">
                                    Número de parte
                                </th>
                                @foreach ($dates as $d)
                                    <th class="px-4 py-3 text-center font-medium text-gray-700 bg-gray-100 w-32">
                                        <div class="flex flex-col items-center">
                                            <span
                                                class="text-sm font-semibold">{{ \Carbon\Carbon::parse($d)->format('d-M') }}</span>
                                            <span
                                                class="text-xs font-normal text-gray-500">{{ \Carbon\Carbon::parse($d)->locale('es')->isoFormat('dddd') }}</span>
                                        </div>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>

                        <tbody class="divide-y">
                            @foreach ($partsData as $part => $pdata)
                                <tr class="hover:bg-gray-50">
                                    <td class="sticky left-0 px-4 py-3 bg-white z-10 w-56">
                                        <button
                                            class="text-left text-sm font-medium text-indigo-700 hover:underline focus:outline-none"
                                            onclick="openPartModal(@js($pdata))">
                                            {{ $part }}
                                        </button>
                                        <div class="text-xs text-gray-500 mt-1">
                                            {{ $pdata['vendor_name'] ?? '' }}
                                        </div>
                                    </td>

                                    @foreach ($dates as $d)
                                        @php
                                            $itemData = $pdata['by_date'][$d] ?? null;
                                            $percentage = $itemData['percentage'] ?? null;

                                            if (is_null($percentage) || $percentage === '') {
                                                $badgeClass = 'bg-gray-50 border border-gray-100 text-gray-500';
                                            } else {
                                                if ($percentage >= 80) {
                                                    $badgeClass = 'bg-green-100 text-green-800 border border-green-200';
                                                } elseif ($percentage >= 60) {
                                                    $badgeClass =
                                                        'bg-yellow-100 text-yellow-800 border border-yellow-200';
                                                } else {
                                                    $badgeClass = 'bg-red-100 text-red-800 border border-red-200';
                                                }
                                            }
                                        @endphp

                                        <td class="px-4 py-3 text-center w-32">
                                            @if (is_null($percentage) || $percentage === '')
                                                <span class="text-xs text-gray-400">—</span>
                                            @else
                                                <span
                                                    class="inline-flex items-center justify-center px-3 py-1 rounded-full text-xs font-medium {{ $badgeClass }}">
                                                    {{ number_format($percentage, 1) }}%
                                                </span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Fila de totales fija en la parte inferior -->
                <div class="bg-gray-50 border-t-2 border-gray-200 flex-shrink-0">
                    <table class="min-w-full table-fixed">
                        <tbody>
                            <tr class="font-semibold">
                                <td class="sticky left-0 px-4 py-3 bg-gray-50 z-10 w-56">TOTAL DE DÍA</td>
                                @foreach ($dates as $d)
                                    @php
                                        $tp = $totalsByDate[$d] ?? null;
                                        if (is_null($tp) || $tp === '') {
                                            $badgeClass = 'bg-gray-50 border border-gray-100 text-gray-500';
                                        } else {
                                            if ($tp >= 80) {
                                                $badgeClass = 'bg-green-200 text-green-900 border border-green-300';
                                            } elseif ($tp >= 60) {
                                                $badgeClass = 'bg-yellow-200 text-yellow-900 border border-yellow-300';
                                            } else {
                                                $badgeClass = 'bg-red-200 text-red-900 border border-red-300';
                                            }
                                        }
                                    @endphp
                                    <td class="px-4 py-3 text-center w-32">
                                        @if (is_null($tp) || $tp === '')
                                            <span class="text-xs text-gray-400">—</span>
                                        @else
                                            <span
                                                class="inline-flex items-center justify-center px-3 py-1 rounded-full text-sm font-medium {{ $badgeClass }}">
                                                {{ number_format($tp, 1) }}%
                                            </span>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para mostrar detalles del parte -->
    <div id="partModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-50 p-4">
        <div class="bg-white rounded-xl shadow-xl max-w-4xl w-full max-h-[90vh] flex flex-col transform transition-all">
            <!-- Header del modal - Fijo -->
            <div class="px-6 py-4 border-b border-gray-200 bg-white flex-shrink-0">
                <div class="flex justify-between items-center">
                    <h3 id="modalTitle" class="text-xl font-semibold text-gray-800">Detalles del Número de Parte</h3>
                    <button onclick="closePartModal()" class="text-gray-400 hover:text-gray-600 transition-colors p-1">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Contenido del modal - Scrollable -->
            <div class="flex-1 overflow-y-auto">
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <!-- Información básica -->
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-600 mb-1">Número de Parte</label>
                                <div id="m_part" class="text-lg font-semibold text-gray-900"></div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-600 mb-1">Clase</label>
                                <div id="m_class" class="text-gray-700"></div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-600 mb-1">Centro de Trabajo</label>
                                <div id="m_work" class="text-gray-700"></div>
                            </div>
                        </div>

                        <!-- Información del proveedor -->
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-600 mb-1">Proveedor</label>
                                <div id="m_vendor" class="text-gray-700"></div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-600 mb-1">Totales Acumulados</label>
                                <div class="grid grid-cols-3 gap-4 mt-2">
                                    <div class="text-center p-3 bg-gray-50 rounded-lg">
                                        <div class="text-xs text-gray-500">Ordenado</div>
                                        <div id="m_ordered" class="text-lg font-semibold text-gray-900"></div>
                                    </div>
                                    <div class="text-center p-3 bg-gray-50 rounded-lg">
                                        <div class="text-xs text-gray-500">Recibido</div>
                                        <div id="m_received" class="text-lg font-semibold text-gray-900"></div>
                                    </div>
                                    <div class="text-center p-3 bg-gray-50 rounded-lg">
                                        <div class="text-xs text-gray-500">Porcentaje</div>
                                        <div id="m_percent" class="text-lg font-semibold"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabla de desglose por día -->
                    <div class="border-t pt-6">
                        <h4 class="text-lg font-medium text-gray-900 mb-4">Desglose por Día</h4>
                        <div class="overflow-x-auto">
                            <table class="min-w-full table-auto border-collapse">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-700 border">Fecha
                                        </th>
                                        <th class="px-4 py-2 text-right text-sm font-medium text-gray-700 border">Cant.
                                            Ordenada</th>
                                        <th class="px-4 py-2 text-right text-sm font-medium text-gray-700 border">Cant.
                                            Recibida</th>
                                        <th class="px-4 py-2 text-right text-sm font-medium text-gray-700 border">
                                            Porcentaje</th>
                                    </tr>
                                </thead>
                                <tbody id="m_table_body" class="bg-white">
                                    <!-- Se llenará con JavaScript -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer del modal - Fijo -->
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex justify-end flex-shrink-0">
                <button onclick="closePartModal()"
                    class="px-6 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors font-medium">
                    Cerrar
                </button>
            </div>
        </div>
    </div>

    <script>
        function openPartModal(data) {
            const modal = document.getElementById('partModal');

            // Llenar información básica
            document.getElementById('m_part').textContent = data.item_number || '-';
            document.getElementById('m_class').textContent = data.item_class || '-';
            document.getElementById('m_vendor').textContent = data.vendor_name ?
                `${data.vendor_number} • ${data.vendor_name} ` : '-';
            document.getElementById('m_work').textContent = data.work_center_name ?
                `${data.work_center_no} • ${data.work_center_name}` : '-';

            // Llenar totales
            document.getElementById('m_ordered').textContent = data.ordered_total?.toLocaleString() || '0';
            document.getElementById('m_received').textContent = data.received_total?.toLocaleString() || '0';

            // Calcular y mostrar porcentaje total
            const totalPercentage = data.ordered_total > 0 ?
                ((data.received_total / data.ordered_total) * 100) : 0;
            const percentElement = document.getElementById('m_percent');
            percentElement.textContent = totalPercentage.toFixed(1) + '%';

            // Color del porcentaje total
            if (totalPercentage >= 80) {
                percentElement.className = 'text-lg font-semibold text-green-600';
            } else if (totalPercentage >= 60) {
                percentElement.className = 'text-lg font-semibold text-yellow-600';
            } else {
                percentElement.className = 'text-lg font-semibold text-red-600';
            }

            // Llenar tabla por día
            const tableBody = document.getElementById('m_table_body');
            const byDate = data.by_date || {};
            let html = '';

            Object.entries(byDate).forEach(([date, itemData]) => {
                const percentage = itemData.percentage || 0;
                let percentageClass = '';

                if (percentage >= 80) {
                    percentageClass = 'text-green-600 font-semibold';
                } else if (percentage >= 60) {
                    percentageClass = 'text-yellow-600 font-semibold';
                } else {
                    percentageClass = 'text-red-600 font-semibold';
                }

                html += `
                    <tr class="border-t hover:bg-gray-50">
                        <td class="px-4 py-2 border">${date}</td>
                        <td class="px-4 py-2 text-right border">${(itemData.ordered_qty || 0).toLocaleString()}</td>
                        <td class="px-4 py-2 text-right border">${(itemData.received_qty || 0).toLocaleString()}</td>
                        <td class="px-4 py-2 text-right border ${percentageClass}">${percentage.toFixed(1)}%</td>
                    </tr>
                `;
            });

            tableBody.innerHTML = html;

            // Mostrar modal
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';
        }

        function closePartModal() {
            const modal = document.getElementById('partModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = 'auto';
        }

        // Cerrar modal con Escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closePartModal();
            }
        });

        // Cerrar modal al hacer click fuera
        document.getElementById('partModal').addEventListener('click', (e) => {
            if (e.target.id === 'partModal') {
                closePartModal();
            }
        });
    </script>
</x-guest-layout>
