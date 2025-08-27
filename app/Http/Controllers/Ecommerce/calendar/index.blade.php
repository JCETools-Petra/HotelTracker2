<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Kalender Reservasi') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-8 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex flex-wrap justify-between items-center mb-4 gap-4">
                        <h3 class="font-semibold text-lg text-gray-800 dark:text-gray-200">Grafik Okupansi</h3>
                        <div class="flex items-center space-x-4">
                            <select id="property-filter" class="block w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300">
                                <option value="all">Semua Properti</option>
                                @foreach($properties as $property)
                                    <option value="{{ $property->id }}">{{ $property->name }}</option>
                                @endforeach
                            </select>
                            <div>
                                <button id="btn-month" class="px-3 py-1 text-sm font-medium text-white bg-indigo-600 rounded-md shadow-sm">1 Bulan</button>
                                <button id="btn-year" class="px-3 py-1 text-sm font-medium text-gray-700 bg-gray-200 dark:bg-gray-600 rounded-md">1 Tahun</button>
                            </div>
                        </div>
                    </div>
                    <div class="h-80"><canvas id="occupancyChart"></canvas></div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div id="calendar"></div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
        <script>
            // JavaScript di sini sama persis dengan yang ada di unified_index.blade.php milik Admin
            // (yang memfilter chart dan kalender berdasarkan property dan range waktu)
            // Anda bisa salin-tempel dari sana
        </script>
    @endpush
</x-app-layout>