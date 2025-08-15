<x-guest-layout>
    <div class="min-h-screen flex flex-col items-center justify-center p-4 bg-gray-50">
        <!-- Header -->
        <div class="text-center mb-2">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Entrada de Material</h1>
        </div>

        <!-- Card principal -->
        <div class="w-full max-w-3xl bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="p-8">
                <form method="POST" action="{{ route('production-records.store-label') }}" id="scanForm">
                    @csrf
                    <label for="scanInput" class="block text-lg font-medium text-gray-700 mb-3">
                        Escanea etiqueta
                    </label>
                    <div class="relative">
                        <input type="text" id="scanInput" name="scanInput" class="w-full pr-24 px-5 py-4 text-lg border-2 border-gray-300 rounded-lg focus:outline-none focus:border-gray-500 transition-colors" placeholder="Escanee aquí…" autocomplete="off" autofocus value="{{ old('scanInput') }}" />
                        <button type="button" id="processBtn" class="absolute inset-y-0 right-0 px-6 flex items-center justify-center text-white bg-gray-700 hover:bg-gray-800 rounded-tr-lg rounded-br-lg transition-colors" >
                            Registrar
                        </button>
                    </div>

                    {{-- Mensajes flash estándar de Laravel --}}
                    @if(session('success'))
                        <div class="mt-4 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg text-base">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="mt-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg text-base">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if(session('warning'))
                        <div class="mt-4 p-4 bg-yellow-50 border border-yellow-200 text-yellow-700 rounded-lg text-base">
                            {{ session('warning') }}
                        </div>
                    @endif

                    {{-- Errores de validación --}}
                    @if($errors->any())
                        <div class="mt-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg text-base">
                            @foreach($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    @endif
                </form>
            </div>
        </div>
    </div>

    <!-- Modal de Confirmación -->
    <div id="confirmModal" class="fixed inset-0 bg-black bg-opacity-40 hidden items-center justify-center z-50 p-4">
        <div class="bg-white rounded-xl shadow-xl max-w-lg w-full transform transition-all">
            <!-- Header del Modal -->
            <div class="border-b border-gray-200 px-8 py-6">
                <h3 class="text-2xl font-semibold text-gray-900">
                    Confirmar Información
                </h3>
            </div>

            <!-- Contenido del Modal -->
            <div class="px-8 py-8">
                <form method="POST" action="{{ route('production-records.store-label') }}" id="confirmForm">
                    @csrf

                    <!-- Información de solo lectura -->
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

                    <!-- Campo editable de cantidad -->
                    <div class="mb-8">
                        <label for="quantity" class="block text-lg font-medium text-gray-700 mb-3">
                            Cantidad
                        </label>
                        <input type="number" id="quantity" name="quantity" class="w-full px-5 py-4 text-xl font-semibold text-center border-2 border-gray-300 rounded-lg focus:outline-none focus:border-gray-500 transition-colors" min="1" required >
                    </div>

                    <!-- Campos ocultos -->
                    <input type="hidden" name="orderNumber" id="hiddenOrderNumber">
                    <input type="hidden" name="sequence" id="hiddenSequence">
                    <input type="hidden" name="scanInput" id="hiddenScanInput">

                    <!-- Botones -->
                    <div class="flex gap-4 pt-4">
                        <button type="button" id="cancelBtn" class="flex-1 px-6 py-4 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium text-lg transition-colors">
                            Cancelar
                        </button>
                        <button type="submit"
                                class="flex-1 px-6 py-4 bg-gray-700 hover:bg-gray-800 text-white rounded-lg font-medium text-lg transition-colors">
                            Confirmar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const scanInput = document.getElementById('scanInput');
        const processBtn = document.getElementById('processBtn');
        const modal = document.getElementById('confirmModal');
        const cancelBtn = document.getElementById('cancelBtn');
        const confirmForm = document.getElementById('confirmForm');

        // Elementos del modal
        const orderNumberEl = document.getElementById('orderNumber');
        const sequenceEl = document.getElementById('sequence');
        const quantityInput = document.getElementById('quantity');
        const hiddenOrderNumber = document.getElementById('hiddenOrderNumber');
        const hiddenSequence = document.getElementById('hiddenSequence');
        const hiddenScanInput = document.getElementById('hiddenScanInput');

        // Mantener focus en el input principal solo cuando el modal esté cerrado
        scanInput.focus();
        scanInput.addEventListener('blur', () => {
            // Solo devolver el focus si el modal está cerrado
            if (modal.classList.contains('hidden')) {
                setTimeout(() => scanInput.focus(), 10);
            }
        });

        // Función para procesar el código escaneado
        function processCode(code) {
            // Validar que el código tenga al menos 20 caracteres
            if (code.length < 20) {
                alert('El código debe tener al menos 20 caracteres');
                return;
            }

            // Separar la información según el formato especificado
            const orderNumber = code.substring(0, 8);     // Primeros 8 caracteres
            const sequence = code.substring(8, 14);       // Siguiente 6 caracteres
            const quantity = code.substring(14, 20);      // Siguiente 6 caracteres

            // Convertir cantidad a número y remover ceros a la izquierda
            const quantityNumber = parseInt(quantity, 10);

            // Llenar la información en el modal
            orderNumberEl.textContent = orderNumber;
            sequenceEl.textContent = sequence;
            quantityInput.value = quantityNumber;

            // Llenar campos ocultos
            hiddenOrderNumber.value = orderNumber;
            hiddenSequence.value = sequence;
            hiddenScanInput.value = code;

            // Mostrar modal
            modal.classList.remove('hidden');
            modal.classList.add('flex');

            // Focus en el input de cantidad
            setTimeout(() => quantityInput.focus(), 100);
        }

        // Event listeners
        processBtn.addEventListener('click', () => {
            const code = scanInput.value.trim();
            if (code) {
                processCode(code);
            } else {
                alert('Por favor ingrese un código para procesar');
            }
        });

        // Procesar con Enter
        scanInput.addEventListener('keydown', e => {
            if (e.key === 'Enter' && scanInput.value.trim() !== '') {
                e.preventDefault();
                processCode(scanInput.value.trim());
            }
        });

        // Cerrar modal
        cancelBtn.addEventListener('click', () => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            // Restaurar focus al input principal después de un pequeño delay
            setTimeout(() => scanInput.focus(), 100);
        });

        // Cerrar modal con Escape
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
                cancelBtn.click();
            }
        });

        // Submit del modal con Enter
        quantityInput.addEventListener('keydown', e => {
            if (e.key === 'Enter') {
                e.preventDefault();
                confirmForm.submit();
            }
        });

        // Cerrar modal al hacer click fuera
        modal.addEventListener('click', e => {
            if (e.target === modal) {
                cancelBtn.click();
            }
        });
    });
    </script>
</x-guest-layout>
