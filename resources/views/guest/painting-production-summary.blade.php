<x-guest-layout>
    <div class="bg-gray-100 dark:bg-gray-900 min-h-screen">
        <!-- Contenedor principal sin padding lateral para aprovechar todo el ancho -->
        <div class="w-full mx-auto">
            <!-- Livewire component ocupa todo el ancho disponible -->
            <div class="w-full">
                <livewire:production-record.paint-production-record :real-time="true" />
            </div>
        </div>
    </div>
</x-guest-layout>
