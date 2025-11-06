<x-guest-layout>
    <div class="min-h-screen p-6 bg-gray-50">
        <div class="max-w-6xl mx-auto">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
                <h1 class="text-2xl font-semibold text-gray-900">Reporte: Entrada de Material (por día)</h1>
                <p class="text-sm text-gray-600">Semana seleccionada — columnas por fecha</p>
            </div>

            <!-- Tabla contenedor con scroll -->
            <div class="bg-white rounded-xl shadow overflow-auto">
                @php
                    // $final es la colección que envias desde el controlador
                    $dates = collect($final)->pluck('date')->map(fn($d) => $d ?? 'N/A')->values()->all();

                    // Construir estructura de partes: partes => datos generales + porcentajes por fecha
                    $partsData = [];
                    foreach ($final as $day) {
                        $dateLabel = $day['date'] ?? $day['date_ymd'] ?? null;
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
                                    'by_date' => [], // fecha => percentage
                                ];
                            }
                            // sumar totales acumulados (si vienen repetidos en varias fechas)
                            $partsData[$part]['ordered_total'] += $item['ordered_qty'] ?? 0;
                            $partsData[$part]['received_total'] += $item['received_qty'] ?? 0;
                            // porcentaje por fecha
                            $partsData[$part]['by_date'][$dateLabel] = $item['percentage'] ?? null;
                        }
                    }

                    // Totales por día (fila TOTAL DE DÍA)
                    $totalsByDate = [];
                    foreach ($final as $day) {
                        $label = $day['date'] ?? $day['date_ymd'] ?? null;
                        $totalsByDate[$label] = $day['percentage_total'] ?? null;
                    }
                @endphp

                <table class="min-w-full table-auto">
                    <thead class="bg-gray-100 sticky top-0">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-gray-700 w-56">Número de parte</th>
                            @foreach ($dates as $d)
                                <th class="px-4 py-3 text-center font-medium text-gray-700">
                                    {{ \Carbon\Carbon::parse($d)->format('d/M') ?? $d }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>

                    <tbody class="divide-y">
                        @foreach ($partsData as $part => $pdata)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <button
                                        class="text-left text-sm font-medium text-indigo-700 hover:underline"
                                        onclick="openPartModal(@json($pdata))"
                                    >
                                        {{ $part }}
                                    </button>
                                    <div class="text-xs text-gray-500 mt-1">
                                        {{ $pdata['work_center_name'] ?? '' }} • {{ $pdata['vendor_name'] ?? '' }}
                                    </div>
                                </td>

                                @foreach ($dates as $d)
                                    @php
                                        $p = $pdata['by_date'][$d] ?? null;
                                        // clase de color por umbrales (puedes cambiar los valores).
                                        if (is_null($p) || $p === '') {
                                            $cellClass = 'text-gray-400';
                                            $badgeClass = 'bg-gray-50 border border-gray-100 text-gray-500';
                                        } else {
                                            // umbrales: >=80 verde, 60-79 amarillo, <60 rojo
                                            if ($p >= 80) {
                                                $badgeClass = 'bg-green-100 text-green-800 border border-green-200';
                                            } elseif ($p >= 60) {
                                                $badgeClass = 'bg-yellow-100 text-yellow-800 border border-yellow-200';
                                            } else {
                                                $badgeClass = 'bg-red-100 text-red-800 border border-red-200';
                                            }
                                            $cellClass = 'text-sm font-medium';
                                        }
                                    @endphp

                                    <td class="px-4 py-3 text-center">
                                        @if (is_null($p) || $p === '')
                                            <span class="text-xs text-gray-400">—</span>
                                        @else
                                            <span class="inline-flex items-center justify-center px-3 py-1 rounded-full text-xs {{ $badgeClass }}">
                                                {{ number_format($p, 2) }}%
                                            </span>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach

                        <!-- Fila de totales del día -->
                        <tr class="bg-gray-50 font-semibold">
                            <td class="px-4 py-3">TOTAL DE DÍA</td>
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
                                <td class="px-4 py-3 text-center">
                                    @if (is_null($tp) || $tp === '')
                                        <span class="text-xs text-gray-400">—</span>
                                    @else
                                        <span class="inline-flex items-center justify-center px-3 py-1 rounded-full text-sm {{ $badgeClass }}">
                                            {{ number_format($tp, 2) }}%
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

    <!-- Modal simple (vanilla JS) -->
    <div id="partModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-50 p-4">
        <div class="bg-white rounded-xl shadow-xl max-w-2xl w-full transform transition-transform">
            <div class="px-6 py-4 border-b">
                <div class="flex justify-between items-center">
                    <h3 id="modalTitle" class="text-xl font-semibold text-gray-800">Detalle Parte</h3>
                    <button onclick="closePartModal()" class="text-gray-600 hover:text-gray-800">Cerrar ✕</button>
                </div>
            </div>

            <div class="p-6 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <div class="text-xs text-gray-500">Número de Parte</div>
                        <div id="m_part" class="text-lg font-medium text-gray-900"></div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500">Clase</div>
                        <div id="m_class" class="text-lg text-gray-700"></div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500">Proveedor</div>
                        <div id="m_vendor" class="text-sm text-gray-700"></div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500">Centro de Trabajo</div>
                        <div id="m_work" class="text-sm text-gray-700"></div>
                    </div>
                </div>

                <div class="border-t pt-4">
                    <div class="text-sm text-gray-600 mb-2">Totales (esta semana)</div>
                    <div class="flex gap-6">
                        <div>
                            <div class="text-xs text-gray-500">Ordenado</div>
                            <div id="m_ordered" class="text-lg font-medium text-gray-900"></div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-500">Recibido</div>
                            <div id="m_received" class="text-lg font-medium text-gray-900"></div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-500">Porcentaje</div>
                            <div id="m_percent" class="text-lg font-medium text-gray-900"></div>
                        </div>
                    </div>
                </div>

                <div class="">
                    <div class="text-sm text-gray-600 mb-2">Desglose por día</div>
                    <div id="m_table" class="w-full overflow-x-auto">
                        <!-- tabla interna poblada por JS -->
                    </div>
                </div>
            </div>

            <div class="px-6 py-4 border-t flex justify-end">
                <button onclick="closePartModal()" class="px-4 py-2 bg-gray-100 rounded-lg hover:bg-gray-200">Cerrar</button>
            </div>
        </div>
    </div>

    <script>
        // Estructura de datos para el modal: se pasará cuando se haga click en un número de parte
        function openPartModal(data) {
            const modal = document.getElementById('partModal');
            document.getElementById('m_part').textContent = data.item_number || '-';
            document.getElementById('m_class').textContent = data.item_class || '-';
            document.getElementById('m_vendor').textContent = (data.vendor_name ? data.vendor_name + ' (' + (data.vendor_number || '') + ')' : '-');
            document.getElementById('m_work').textContent = (data.work_center_name ? data.work_center_name + ' (' + (data.work_center_no || '') + ')' : '-');

            document.getElementById('m_ordered').textContent = (data.ordered_total ?? 0);
            document.getElementById('m_received').textContent = (data.received_total ?? 0);

            let percent = 0;
            if ((data.ordered_total ?? 0) > 0) {
                percent = ((data.received_total / data.ordered_total) * 100);
            }
            document.getElementById('m_percent').textContent = (isFinite(percent) ? percent.toFixed(2) + '%' : '-');

            // Construir tabla por día
            const tableContainer = document.getElementById('m_table');
            const byDate = data.by_date || {};
            let html = '<table class="min-w-full text-sm"><thead><tr class="text-left"><th class="px-3 py-2">Fecha</th><th class="px-3 py-2 text-right">Porcentaje</th></tr></thead><tbody>';
            for (const [date, pct] of Object.entries(byDate)) {
                const display = (pct === null || pct === undefined) ? '—' : (parseFloat(pct).toFixed(2) + '%');
                html += `<tr class="border-t"><td class="px-3 py-2">${date}</td><td class="px-3 py-2 text-right">${display}</td></tr>`;
            }
            html += '</tbody></table>';
            tableContainer.innerHTML = html;

            // mostrar modal
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closePartModal() {
            const modal = document.getElementById('partModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        // Cerrar con Escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                const modal = document.getElementById('partModal');
                if (!modal.classList.contains('hidden')) closePartModal();
            }
        });
    </script>
</x-guest-layout>
