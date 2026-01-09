<x-guest-layout>
    <div class="min-h-screen flex flex-col items-center justify-center p-4 bg-gray-50">
        <div class="w-full max-w-4xl flex flex-col sm:flex-row justify-between items-center mb-6 gap-4">
            <h1 class="text-3xl font-bold text-gray-900 text-center sm:text-left">Entrada de Material</h1>
            <div class="flex flex-wrap gap-2 justify-center">
                <a href="{{ route('production-records.part-number-entry', ['origin' => url()->current()]) }}"
                    class="inline-flex items-center gap-2 px-4 py-3 text-gray-900 border border-gray-300 rounded-lg hover:border-gray-400 hover:bg-gray-50 transition-colors shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                    </svg>
                    Captura Manual
                </a>
            </div>
        </div>

        <div class="w-full max-w-4xl bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="p-8">
                <form method="POST" action="{{ route('production-records.store-entry') }}" id="scanForm">
                    @csrf
                    <label for="entryCode" class="block text-lg font-medium text-gray-700 mb-3">Escanea etiqueta</label>
                    <div class="relative">
                        <input type="text" id="entryCode" name="entryCode"
                            class="w-full pr-24 px-5 py-4 text-lg border-2 border-gray-300 rounded-lg focus:outline-none focus:border-gray-500 transition-colors"
                            placeholder="Escanee aquí…" autocomplete="off" autofocus value="{{ old('entryCode') }}" />
                        <button type="button" id="processBtn"
                            class="absolute inset-y-0 right-0 px-6 flex items-center justify-center text-white bg-gray-600 hover:bg-gray-700 rounded-tr-lg rounded-br-lg transition-colors font-medium">
                            Registrar
                        </button>
                    </div>

                    @if (session('success'))
                        <div class="mt-4 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg">{{ session('success') }}</div>
                    @endif
                    @if (session('error'))
                        <div class="mt-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg">{{ session('error') }}</div>
                    @endif
                    @if (session('warning'))
                        <div class="mt-4 p-4 bg-yellow-50 border border-yellow-200 text-yellow-700 rounded-lg">{{ session('warning') }}</div>
                    @endif
                </form>
            </div>
        </div>
    </div>

    <div id="confirmModal" class="fixed inset-0 bg-black bg-opacity-40 hidden items-center justify-center z-50 p-4">
        <div class="bg-white rounded-xl shadow-xl max-w-lg w-full transform transition-all">
            <div class="border-b border-gray-200 px-8 py-6">
                <h3 class="text-2xl font-semibold text-gray-900">Confirmar Información</h3>
            </div>
            <div class="px-8 py-8">
                <form method="POST" action="{{ route('production-records.store-entry') }}" id="confirmForm">
                    @csrf
                    <div class="space-y-6 mb-8">
                        <div class="grid grid-cols-2 gap-6">
                            <div class="bg-gray-100 p-5 rounded-lg">
                                <label class="block text-sm font-medium text-gray-600 mb-2">Número de Orden</label>
                                <p class="text-2xl font-bold text-gray-900" id="orderNumber"></p>
                            </div>
                            <div class="bg-gray-100 p-5 rounded-lg">
                                <label class="block text-sm font-medium text-gray-600 mb-2">Secuencia</label>
                                <p class="text-2xl font-bold text-gray-900" id="sequence"></p>
                            </div>
                        </div>
                    </div>
                    <div class="mb-8">
                        <label for="quantity" class="block text-lg font-medium text-gray-700 mb-3">Cantidad</label>
                        <input type="number" id="quantity" name="quantity"
                            class="w-full px-5 py-4 text-xl font-semibold text-center border-2 border-gray-300 rounded-lg focus:outline-none focus:border-gray-500 transition-colors"
                            min="1" step="any" required>
                    </div>
                    <input type="hidden" name="orderNumber" id="hiddenOrderNumber">
                    <input type="hidden" name="sequence" id="hiddenSequence">
                    <input type="hidden" name="entryCode" id="hiddenentryCode">
                    <div class="flex gap-4 pt-4">
                        <button type="button" id="cancelBtn" class="flex-1 px-6 py-4 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium text-lg transition-colors">Cancelar</button>
                        <button type="submit" id="saveBtn" class="flex-1 px-6 py-4 bg-gray-700 hover:bg-gray-800 text-white rounded-lg font-medium text-lg transition-colors flex items-center justify-center">
                            <span id="saveText">Guardar</span>
                            <div id="saveSpinner" class="hidden ml-2">
                                <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-white"></div>
                            </div>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="loadingOverlay" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-xl shadow-2xl p-8 max-w-sm w-full mx-4 flex flex-col items-center space-y-4">
            <div class="animate-spin rounded-full h-16 w-16 border-b-2 border-gray-700"></div>
            <p class="text-lg font-semibold text-gray-800">Procesando...</p>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const entryCode = document.getElementById('entryCode');
            const processBtn = document.getElementById('processBtn');
            const modal = document.getElementById('confirmModal');
            const cancelBtn = document.getElementById('cancelBtn');
            const confirmForm = document.getElementById('confirmForm');
            const saveBtn = document.getElementById('saveBtn');
            const saveText = document.getElementById('saveText');
            const saveSpinner = document.getElementById('saveSpinner');
            const loadingOverlay = document.getElementById('loadingOverlay');

            const orderNumberEl = document.getElementById('orderNumber');
            const sequenceEl = document.getElementById('sequence');
            const quantityInput = document.getElementById('quantity');
            const hiddenOrderNumber = document.getElementById('hiddenOrderNumber');
            const hiddenSequence = document.getElementById('hiddenSequence');
            const hiddenentryCode = document.getElementById('hiddenentryCode');

            function showLoading() { loadingOverlay.classList.replace('hidden', 'flex'); }
            function showSaveLoading() {
                saveText.classList.add('hidden');
                saveSpinner.classList.remove('hidden');
                saveBtn.disabled = true;
            }

            entryCode.focus();
            entryCode.addEventListener('blur', () => {
                if (modal.classList.contains('hidden')) setTimeout(() => entryCode.focus(), 10);
            });

            function processCode(code) {
                let orderNumber, sequence, quantity;

                if (code.includes(',')) {
                    const parts = code.split(',');
                    if (parts.length >= 5) {
                        orderNumber = parts[1].trim();
                        quantity = parts[3].trim();
                        sequence = parts[4].trim();
                    } else {
                        alert('El código separado por comas no tiene el formato esperado.');
                        return;
                    }
                } else if (code.length >= 20 && code.length <= 25) {
                    orderNumber = code.substring(0, 8);
                    sequence = code.substring(8, 14);
                    quantity = code.substring(14, 20);
                } else if (code.length > 25 && code.startsWith('1')) {
                    const relevantPart = code.substring(1);
                    orderNumber = relevantPart.substring(0, 12);
                    sequence = relevantPart.substring(12, 18);
                    quantity = relevantPart.substring(18, 24);
                } else {
                    alert('Formato de código no reconocido.');
                    return;
                }

                const quantityNumber = parseFloat(quantity);
                if (isNaN(quantityNumber) || quantityNumber <= 0) {
                    alert('La cantidad no es válida');
                    return;
                }

                orderNumberEl.textContent = orderNumber;
                sequenceEl.textContent = sequence;
                quantityInput.value = quantityNumber;
                hiddenOrderNumber.value = orderNumber;
                hiddenSequence.value = sequence;
                hiddenentryCode.value = code;

                modal.classList.replace('hidden', 'flex');
                setTimeout(() => quantityInput.focus(), 100);
            }

            processBtn.addEventListener('click', () => {
                const code = entryCode.value.trim();
                if (code) processCode(code);
            });

            entryCode.addEventListener('keydown', e => {
                if (e.key === 'Enter' && entryCode.value.trim() !== '') {
                    e.preventDefault();
                    processCode(entryCode.value.trim());
                }
            });

            cancelBtn.addEventListener('click', () => {
                modal.classList.replace('flex', 'hidden');
                setTimeout(() => entryCode.focus(), 100);
            });

            confirmForm.addEventListener('submit', () => {
                showSaveLoading();
                showLoading();
            });
        });
    </script>
</x-guest-layout>
