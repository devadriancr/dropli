<x-guest-layout>
    <div class="pt-4 bg-gray-100 dark:bg-gray-900">
        <div class="min-h-screen flex flex-col items-center pt-6 sm:pt-0">
            <div class="w-full max-w-7xl px-6 lg:px-8">
                <!-- Grid de 2 columnas: Salida izquierda, Entrada derecha -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Proceso de Salida - Lado Izquierdo -->
                    <div class="w-full">
                        <livewire:production-record.painting-process-exit />
                    </div>

                    <!-- Proceso de Entrada - Lado Derecho -->
                    <div class="w-full">
                        <livewire:production-record.painting-process-entry/>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>
