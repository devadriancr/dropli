<x-guest-layout>
    <div class="min-h-screen flex flex-col items-center justify-center p-4 bg-gray-50">
        <!-- Header -->
        <div class="w-full max-w-3xl flex justify-between items-center mb-2">
            <h1 class="text-3xl font-bold text-gray-900">Entrada de Material</h1>
            <a href="{{ route('production-records.scan-label') }}" class="inline-flex items-center justify-center gap-2 w-full max-w-[200px] py-2 text-white bg-gray-700 hover:bg-gray-800 rounded-lg text-sm transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none"viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" />
                </svg>
                Regresar
            </a>
        </div>

        <!-- Card principal -->
        <div class="w-full max-w-3xl bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="p-8">
                <form method="POST" action="{{ route('production-records.store-part-number') }}" id="scanForm">
                    @csrf

                    <div class="flex flex-row gap-4 mb-6">
                        <div class="w-1/2">
                            <label for="partNumber" class="block text-lg font-medium text-gray-700 mb-3">
                                Número de Parte
                            </label>
                            <div class="relative">
                                <input type="text" id="partNumber" name="partNumber" class="w-full px-5 py-4 text-lg border-2 border-gray-300 rounded-lg focus:outline-none focus:border-gray-500 transition-colors" placeholder="Número de parte..." autocomplete="off" autofocus value="{{ old('partNumber') }}" />
                            </div>
                        </div>

                        <div class="w-1/2">
                            <label for="quantity" class="block text-lg font-medium text-gray-700 mb-3">
                                Cantidad
                            </label>
                            <div class="relative">
                                <input type="number" id="quantity" name="quantity" class="w-full px-5 py-4 text-lg border-2 border-gray-300 rounded-lg focus:outline-none focus:border-gray-500 transition-colors" placeholder="Cantidad..." min="1" required value="{{ old('quantity') }}" />
                            </div>
                        </div>
                    </div>

                    <!-- Botón de envío -->
                    <div class="mt-8">
                        <button type="submit"
                            class="w-full px-6 py-4 text-white bg-gray-700 hover:bg-gray-800 rounded-lg font-medium text-lg transition-colors">
                            Registrar
                        </button>
                    </div>

                    {{-- Mensajes flash --}}
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

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const partNumberInput = document.getElementById('partNumber');

            // Mantener focus en el input de número de parte
            partNumberInput.focus();

            // Permitir enviar el formulario con Enter en el campo de cantidad
            document.getElementById('quantity').addEventListener('keydown', e => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    document.getElementById('scanForm').submit();
                }
            });

            // Al presionar Enter en partNumber, pasar a quantity
            partNumberInput.addEventListener('keydown', e => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    document.getElementById('quantity').focus();
                }
            });
        });
    </script>
</x-guest-layout>
