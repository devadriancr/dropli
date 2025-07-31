<x-guest-layout>
    <div class="min-h-screen flex flex-col items-center justify-center p-4 bg-gray-50">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Entrada de Material</h1>
        </div>

        <div class="w-full max-w-md bg-white rounded-xl shadow-xl overflow-hidden">
            <div class="p-6">
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
                            class="w-full pr-20 px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition"
                            placeholder="Escanee aquí…"
                            autocomplete="off"
                            autofocus
                            value="{{ old('scanInput') }}"
                        />

                        <!-- Botón de envío visible a la derecha -->
                        <button
                            type="submit"
                            form="scanForm"
                            class="absolute inset-y-0 right-0 px-4 flex items-center justify-center text-white bg-blue-600 hover:bg-blue-700 rounded-tr-lg rounded-br-lg"
                        >
                            Enviar
                        </button>
                    </div>

                    {{-- Mensajes flash --}}
                    @if(session('message'))
                        @php $type = session('messageType', 'success'); @endphp
                        <div class="mt-3 p-3 rounded-lg text-sm
                            {{ $type==='success' ? 'bg-green-50 border-green-200 text-green-700' : '' }}
                            {{ $type==='warning' ? 'bg-yellow-50 border-yellow-200 text-yellow-700' : '' }}
                            {{ $type==='error'   ? 'bg-red-50 border-red-200 text-red-700' : '' }}
                        ">
                            {{ session('message') }}
                        </div>
                    @endif

                    {{-- Errores de validación --}}
                    @if($errors->any())
                        <div class="mt-3 p-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
                            @foreach($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    @endif
                </form>
            </div>
        </div>
    </div>

    {{-- JS para autofocus y envío con Enter --}}
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const scanInput = document.getElementById('scanInput');
        const form = document.getElementById('scanForm');

        scanInput.focus();

        scanInput.addEventListener('blur', () => {
            setTimeout(() => scanInput.focus(), 10);
        });

        scanInput.addEventListener('keydown', e => {
            if (e.key === 'Enter' && scanInput.value.trim() !== '') {
                e.preventDefault();
                form.submit();
            }
        });
    });
    </script>
</x-guest-layout>
