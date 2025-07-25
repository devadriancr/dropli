<x-guest-layout>
    <div class="min-h-screen flex flex-col items-center justify-center p-4 bg-gray-50">
        <div class="mx-auto w-1/2 max-w-md bg-white rounded-xl shadow-xl overflow-hidden">
            <div class="bg-white p-6 rounded-lg shadow-md">
                <form method="POST" action="{{ route('production-records.store-label') }}">
                    @csrf

                    <label for="scanInput" class="block text-sm font-medium text-gray-700 mb-1">
                        Escanea etiqueta
                    </label>

                    <input
                        type="text"
                        id="scanInput"
                        name="scanInput"
                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150"
                    />

                    <button type="submit" class="hidden"></button>
                </form>
            </div>
        </div>
    </div>

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
        });
    </script>
</x-guest-layout>
