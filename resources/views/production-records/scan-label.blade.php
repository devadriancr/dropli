<x-guest-layout>
    <div class="min-h-screen flex flex-col items-center justify-center p-4 bg-gray-50">

        <!-- Notificaciones simples -->
        @if(session('success') || session('error') || session('warning') || $errors->any())
            <div class="fixed top-4 right-4 z-50 max-w-sm w-full">
                @if(session('success'))
                    <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg shadow-lg mb-4 notification">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('warning'))
                    <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded-lg shadow-lg mb-4 notification">
                        {{ session('warning') }}
                    </div>
                @endif

                @if(session('error') || $errors->any())
                    <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg shadow-lg mb-4 notification">
                        @if(session('error'))
                            {{ session('error') }}
                        @endif
                        @if($errors->any())
                            @foreach($errors->all() as $error)
                                {{ $error }}
                            @endforeach
                        @endif
                    </div>
                @endif
            </div>
        @endif

        <div class="mx-auto w-1/2 max-w-md bg-white rounded-xl shadow-xl overflow-hidden">
            <div class="bg-white p-6 rounded-lg shadow-md">
                <form method="POST" action="{{ route('production-records.store-label') }}">
                    @csrf

                    <label for="scanInput" class="block text-sm font-medium text-gray-700 mb-2">
                        Escanea etiqueta
                    </label>

                    <div class="relative">
                        <input
                            type="text"
                            id="scanInput"
                            name="scanInput"
                            value="{{ old('scanInput') }}"
                            class="w-full px-4 py-3 pr-10 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150 @error('scanInput') border-red-300 focus:ring-red-500 focus:border-red-500 @enderror"
                            placeholder="Escanee aquí..."
                            autocomplete="off"
                        />
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h4"/>
                            </svg>
                        </div>
                    </div>

                    <button type="submit" class="hidden"></button>
                </form>
            </div>
        </div>
    </div>

    <style>
        .notification {
            animation: slideIn 0.4s ease-out, fadeOut 0.3s ease-in 4s forwards;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes fadeOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const scanInput = document.getElementById('scanInput');
            const form = scanInput.closest('form');

            setTimeout(() => {
                scanInput.focus();
            }, 100);

            scanInput.addEventListener('blur', () => {
                setTimeout(() => {
                    scanInput.focus();
                }, 10);
            });

            scanInput.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    form.submit();
                    scanInput.value = '';
                    scanInput.focus();
                }
            });

            // Limpiar input en éxito
            @if(session('success'))
                scanInput.value = '';
                scanInput.focus();
            @endif
        });
    </script>
</x-guest-layout>
