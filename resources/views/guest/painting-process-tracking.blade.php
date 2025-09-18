<x-guest-layout>
    <div class="bg-gray-50 dark:bg-gray-900 min-h-screen">
        <div class="flex flex-col items-center p-4 sm:pt-0 min-h-screen">
            <div class="w-full max-w-7xl p-6 lg:px-8 grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Proceso de Salida - Lado Izquierdo -->
                <div class="w-full">
                    <livewire:production-record.painting-process-exit />
                </div>

                <!-- Proceso de Entrada - Lado Derecho -->
                <div class="w-full">
                    <livewire:production-record.painting-process-entry />
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>
