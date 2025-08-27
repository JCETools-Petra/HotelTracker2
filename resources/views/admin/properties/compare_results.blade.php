<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Hasil Perbandingan Properti') }}
            </h2>
            <nav>
                {{-- PERUBAHAN: Menggunakan nama route yang sudah diperbaiki --}}
                <x-nav-link :href="route('admin.properties.compare_page')" class="ml-3">
                    {{ __('Buat Perbandingan Baru') }}
                </x-nav-link>
                <x-nav-link :href="route('admin.dashboard')" class="ml-3">
                    {{ __('Kembali ke Dashboard Admin') }}
                </x-nav-link>
            </nav>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold">Kriteria Perbandingan:</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            <strong>Properti:</strong>
                            @foreach ($selectedPropertiesModels as $prop)
                                {{ $prop->name }}{{ !$loop->last ? ', ' : '' }}
                            @endforeach
                        </p>
                        <p class="text-sm text-gray-600 dark:text-gray-400"><strong>Periode:</strong> {{ $startDateFormatted }} - {{ $endDateFormatted }}</p>
                    </div>

                    {{-- ========================================================================= --}}
                    {{-- >> AWAL TAMBAHAN YANG ANDA MINTA << --}}
                    {{-- ========================================================================= --}}
                    <div class="mb-8 p-4 border dark:border-gray-700 rounded-lg">
                        <h3 class="text-xl font-semibold text-gray-700 dark:text-gray-300 mb-4">Perbandingan Detail per Sumber Pendapatan</h3>
                        
                        <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg mb-4 border border-gray-200 dark:border-gray-600">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tampilkan Kategori:</label>
                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                                @foreach($incomeCategories as $key => $label)
                                    <div class="flex items-center">
                                        <input type="checkbox" id="check_{{ $key }}" value="{{ $key }}" class="category-toggle h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" checked>
                                        <label for="check_{{ $key }}" class="ml-2 block text-sm text-gray-900 dark:text-gray-200">{{ $label }}</label>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Sumber Pendapatan</th>
                                        @foreach($selectedPropertiesModels as $property)
                                            <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ $property->name }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody id="detailed-comparison-body" class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($incomeCategories as $key => $label)
                                        <tr id="row_{{ $key }}" class="category-row hover:bg-gray-50 dark:hover:bg-gray-600">
                                            <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">{{ $label }}</td>
                                            @foreach($selectedPropertiesModels as $property)
                                                @php
                                                    $propertyData = collect($comparisonData)->firstWhere('name', $property->name);
                                                @endphp
                                                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300 text-right">
                                                    Rp {{ number_format($propertyData[$key] ?? 0, 0, ',', '.') }}
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                     <tr class="bg-gray-100 dark:bg-gray-900 font-bold">
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">TOTAL</td>
                                        @foreach($selectedPropertiesModels as $property)
                                            @php
                                                $propertyData = collect($comparisonData)->firstWhere('name', $property->name);
                                            @endphp
                                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 text-right">
                                                Rp {{ number_format($propertyData['total_revenue'] ?? 0, 0, ',', '.') }}
                                            </td>
                                        @endforeach
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    {{-- ========================================================================= --}}
                    {{-- >> AKHIR TAMBAHAN YANG ANDA MINTA << --}}
                    {{-- ========================================================================= --}}


                    {{-- Chart Perbandingan Kategori Pendapatan (Grouped Bar Chart) --}}
                    <div class="mt-10 mb-8 p-4 border dark:border-gray-700 rounded-lg">
                        <h4 class="text-lg font-medium text-gray-700 dark:text-gray-300 mb-3">Grafik Perbandingan Pendapatan per Kategori</h4>
                        <div style="height: 450px;">
                            <canvas id="propertiesCategoryComparisonChart"></canvas>
                        </div>
                    </div>

                    {{-- Chart Perbandingan Tren Pendapatan Harian (Multi-Line Chart) --}}
                    <div class="mb-8 p-4 border dark:border-gray-700 rounded-lg">
                        <h4 class="text-lg font-medium text-gray-700 dark:text-gray-300 mb-3">Grafik Perbandingan Tren Pendapatan Harian</h4>
                        <div style="height: 450px;">
                            <canvas id="propertiesTrendComparisonChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const chartDataGroupedBarPHP = @json($chartDataGroupedBar);
    const trendChartDataPHP = @json($trendChartData);

    // 1. Grafik Batang Terkelompok: Perbandingan Pendapatan per Kategori
    const categoryComparisonCanvas = document.getElementById('propertiesCategoryComparisonChart');
    if (categoryComparisonCanvas && chartDataGroupedBarPHP && chartDataGroupedBarPHP.datasets.length > 0) {
        new Chart(categoryComparisonCanvas, {
            type: 'bar',
            data: chartDataGroupedBarPHP,
            options: {
                responsive: true, maintainAspectRatio: false,
                scales: { y: { beginAtZero: true, ticks: { callback: value => 'Rp ' + value.toLocaleString('id-ID') } } },
                plugins: {
                    legend: { display: true, position: 'top' },
                    title: { display: false },
                    tooltip: { callbacks: { label: context => `${context.dataset.label}: Rp ${context.parsed.y.toLocaleString('id-ID')}` } }
                }
            }
        });
    }

    // 2. Grafik Garis: Perbandingan Tren Pendapatan Harian
    const trendComparisonCanvas = document.getElementById('propertiesTrendComparisonChart');
    if (trendComparisonCanvas && trendChartDataPHP && trendChartDataPHP.datasets.length > 0) {
        new Chart(trendComparisonCanvas, {
            type: 'line',
            data: trendChartDataPHP,
            options: {
                responsive: true, maintainAspectRatio: false,
                scales: { y: { beginAtZero: true, ticks: { callback: value => 'Rp ' + value.toLocaleString('id-ID') } } },
                plugins: {
                    legend: { display: true, position: 'top' },
                    title: { display: false },
                    tooltip: { mode: 'index', intersect: false, callbacks: { label: context => `${context.dataset.label}: Rp ${context.parsed.y.toLocaleString('id-ID')}` } }
                },
                interaction: { mode: 'nearest', axis: 'x', intersect: false }
            }
        });
    }

    // =====================================================================
    // >> JAVASCRIPT UNTUK FITUR BARU <<
    // =====================================================================
    const toggles = document.querySelectorAll('.category-toggle');
    toggles.forEach(function(toggle) {
        toggle.addEventListener('change', function() {
            const categoryKey = this.value;
            const row = document.getElementById('row_' + categoryKey);
            if (row) {
                row.style.display = this.checked ? '' : 'none';
            }
        });
    });
});
</script>
@endpush
</x-app-layout>