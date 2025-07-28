<x-guest-layout>
    <div class="min-h-screen flex flex-col items-center justify-center p-4 bg-gray-50">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Entrada de Material</h1>
        </div>

        <div class="mx-auto w-1/2 max-w-md bg-white rounded-xl shadow-xl overflow-hidden">
            <div class="bg-white p-6 rounded-lg shadow-md">
                <form method="POST" action="{{ route('production-records.store-label') }}" id="scanForm">
                    @csrf

                    <label for="scanInput" class="block text-sm font-medium text-gray-700 mb-2">
                        Escanea etiqueta
                    </label>

                    <div class="relative">
                        <input
                            type="text"
                            id="scanInput"
                            name="scanInput"
                            class="w-full px-4 py-3 pr-10 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150"
                            placeholder="Escanee aquí..."
                            autocomplete="off"
                            autofocus
                            value="{{ old('scanInput') }}"
                        />
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h4"/>
                            </svg>
                        </div>
                    </div>

                    <!-- Mensajes debajo del input -->
                    @isset($message)
                        @if($messageType === 'success')
                            <div class="mt-3 p-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">
                                {{ $message }}
                            </div>
                        @elseif($messageType === 'warning')
                            <div class="mt-3 p-3 bg-yellow-50 border border-yellow-200 text-yellow-700 rounded-lg text-sm">
                                {{ $message }}
                            </div>
                        @elseif($messageType === 'error')
                            <div class="mt-3 p-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
                                {{ $message }}
                            </div>
                        @endif
                    @endisset

                    @if($errors->any())
                        <div class="mt-3 p-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
                            @foreach($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    @endif

                    <button type="submit" class="hidden"></button>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const scanInput = document.getElementById('scanInput');
            const form = document.getElementById('scanForm');

            // Mantener el autofocus y limpiar el campo después de mostrar mensajes de éxito
            setTimeout(() => {
                scanInput.focus();
                // Limpiar el campo si hay mensaje de éxito
                @isset($messageType)
                    @if($messageType === 'success')
                        scanInput.value = '';
                    @endif
                @endisset
            }, 100);

            scanInput.addEventListener('blur', () => {
                setTimeout(() => {
                    scanInput.focus();
                }, 10);
            });

            // Envío automático con Enter
            scanInput.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    if (scanInput.value.trim() !== '') {
                        form.submit();
                    }
                }
            });
        });
    </script>
</x-guest-layout>
