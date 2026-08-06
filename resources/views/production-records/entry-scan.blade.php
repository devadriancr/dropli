<x-guest-layout>
    <div class="min-h-screen flex flex-col items-center justify-center p-4 bg-gray-50">
        <div class="w-full max-w-4xl flex flex-col sm:flex-row justify-between items-center mb-6 gap-4">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-blue-600" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v3.586L7.707 9.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 10.586V7z"
                            clip-rule="evenodd" />
                    </svg>
                </div>
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 text-center sm:text-left">Entrada de Material</h1>
            </div>
            <div class="flex flex-wrap gap-2 justify-center">
                <a href="{{ route('production-records.summary') }}"
                    class="inline-flex items-center gap-2 px-4 py-3 text-gray-900 border border-gray-300 rounded-lg hover:border-gray-400 hover:bg-gray-50 transition-colors shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                    </svg>
                    Inicio
                </a>
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
            <div class="p-6 sm:p-8">
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

                    @include('production-records.partials.flash-messages')
                </form>
            </div>
        </div>
    </div>

    <div id="confirmModal" class="fixed inset-0 bg-black bg-opacity-40 hidden items-center justify-center z-50 p-4">
        <div class="bg-white rounded-xl shadow-2xl max-w-lg w-full transform transition-all">
            <div class="px-8 pt-8 pb-2 text-center">
                <div class="mx-auto mb-4 w-14 h-14 rounded-full bg-blue-100 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 text-blue-600" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v3.586L7.707 9.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 10.586V7z"
                            clip-rule="evenodd" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900">Confirmar Información</h3>
            </div>
            <div class="px-8 pb-8 pt-2">
                <form method="POST" action="{{ route('production-records.store-entry') }}" id="confirmForm">
                    @csrf
                    <div class="space-y-6 mb-8">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
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
                        <button type="submit" id="saveBtn" class="flex-1 px-6 py-4 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium text-lg transition-colors flex items-center justify-center">
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
