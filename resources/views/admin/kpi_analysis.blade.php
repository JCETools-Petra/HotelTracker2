<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Pusat Analisis Kinerja (KPI)') }}
            </h2>
            <nav class="flex space-x-4">
                <x-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">
                    {{ __('Dashboard Utama') }}
                </x-nav-link>
            </nav>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            {{-- Filter Global --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Filter Data</h3>
                <form method="GET" action="{{ route('admin.kpi.analysis') }}">
                    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4 items-end">
                        <div>
                            <x-input-label for="start_date_filter" :value="__('Dari Tanggal')" />
                            <x-text-input id="start_date_filter" class="block mt-1 w-full" type="date" name="start_date" :value="$filterStartDate ? $filterStartDate->format('Y-m-d') : ''" />
                        </div>
                        <div>
                            <x-input-label for="end_date_filter" :value="__('Sampai Tanggal')" />
                            <x-text-input id="end_date_filter" class="block mt-1 w-full" type="date" name="end_date" :value="$filterEndDate ? $filterEndDate->format('Y-m-d') : ''" />
                        </div>
                        <div>
                            <x-input-label for="property_mom_filter_id" :value="__('Properti untuk Analisis Detail')" />
                            <select name="property_mom_filter_id" id="property_mom_filter_id" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700 dark:text-gray-200">
                                <option value="all" {{ ($propertyMomFilterId == 'all' || !$propertyMomFilterId) ? 'selected' : '' }}>Semua Properti (Gabungan)</option>
                                @if(isset($allPropertiesForFilter))
                                    @foreach ($allPropertiesForFilter as $property)
                                        <option value="{{ $property->id }}" {{ $propertyMomFilterId == $property->id ? 'selected' : '' }}>
                                            {{ $property->name }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="pt-5 md:pt-0">
                            <x-primary-button type="submit">{{ __('Terapkan Filter') }}</x-primary-button>
                            <a href="{{ route('admin.kpi.analysis') }}"
                               class="ml-2 inline-flex items-center px-4 py-2 bg-gray-300 dark:bg-gray-500 border border-transparent rounded-md font-semibold text-xs text-gray-700 dark:text-gray-100 uppercase tracking-widest hover:bg-gray-400 dark:hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                {{ __('Reset') }}
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            {{-- Ringkasan Kinerja Keseluruhan --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-2">Ringkasan Kinerja Keseluruhan</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">
                    Berdasarkan periode: {{ $filterStartDate->isoFormat('D MMMM YYYY') }} - {{ $filterEndDate->isoFormat('D MMMM YYYY') }} (Total {{ $totalDaysInPeriod }} hari)
                </p>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                    <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg shadow">
                        <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Pendapatan</h4>
                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">Rp {{ number_format($totalOverallRevenue ?? 0, 0, ',', '.') }}</p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg shadow">
                        <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Rata-Rata Pendapatan Harian</h4>
                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">Rp {{ number_format($averageDailyOverallRevenue ?? 0, 0, ',', '.') }}</p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg shadow">
                        <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Jml. Properti Aktif</h4>
                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $activePropertiesCount ?? 0 }}</p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg shadow">
                        <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Jml. Pengguna Properti</h4>
                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $activePropertyUsersCount ?? 0 }}</p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg shadow">
                        <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Rata-Rata Pendapatan/Properti</h4>
                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">Rp {{ number_format($averageRevenuePerProperty ?? 0, 0, ',', '.') }}</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="p-4 border dark:border-gray-700 rounded-lg">
                        <h4 class="text-md font-medium text-gray-700 dark:text-gray-300 mb-2">Distribusi Sumber Pendapatan (Periode Terfilter)</h4>
                        <div style="height: 300px;">
                            <canvas id="kpiOverallSourcePieChart"></canvas>
                        </div>
                    </div>
                    <div class="p-4 border dark:border-gray-700 rounded-lg">
                        <h4 class="text-md font-medium text-gray-700 dark:text-gray-300 mb-2">Total Pendapatan per Properti (Periode Terfilter)</h4>
                        <div style="height: 300px;">
                            <canvas id="kpiOverallIncomeByPropertyBarChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Analisis Detail per Kategori Pendapatan --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-2">Analisis Detail per Kategori Pendapatan</h3>
                 <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">
                    Berdasarkan periode: {{ $filterStartDate->isoFormat('D MMMM YYYY') }} - {{ $filterEndDate->isoFormat('D MMMM YYYY') }} (Total {{ $totalDaysInPeriod }} hari)
                </p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div class="p-4 border dark:border-gray-700 rounded-lg">
                        <h4 class="text-md font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Tren Total Pendapatan per Kategori
                            @if($propertyMomFilterId && $propertyMomFilterId != 'all' && isset($allPropertiesForFilter))
                                - {{ $allPropertiesForFilter->firstWhere('id', $propertyMomFilterId)->name ?? '' }}
                            @else
                                - Gabungan Semua Properti
                            @endif
                        </h4>
                        <div style="height: 350px;">
                            @if(!empty($trendKontribusiData['labels']))
                                <canvas id="monthlyCategoryTrendChart"></canvas>
                            @else
                                <p class="text-sm text-gray-500 dark:text-gray-400 flex items-center justify-center h-full">Data tren tidak cukup untuk periode/properti yang dipilih.</p>
                            @endif
                        </div>
                    </div>
                    
                    <div class="p-4 border dark:border-gray-700 rounded-lg space-y-2 overflow-y-auto" style="max-height: 414px;">
                        <h4 class="text-md font-medium text-gray-700 dark:text-gray-300 mb-3 sticky top-0 bg-white dark:bg-gray-700 py-2">
                            Pertumbuhan Kategori Pendapatan (MoM)
                            <span class="block text-xs font-normal">
                                @if($propertyMomFilterId && $propertyMomFilterId != 'all' && isset($allPropertiesForFilter))
                                    - {{ $allPropertiesForFilter->firstWhere('id', $propertyMomFilterId)->name ?? '' }}
                                @else
                                    - Gabungan Semua Properti
                                @endif
                            </span>
                        </h4>
                        @if (!empty($multiMonthCategoryGrowth))
                            @forelse ($multiMonthCategoryGrowth as $monthName => $growthData)
                                <div class="mb-3 bg-gray-50 dark:bg-gray-700/50 p-3 rounded-md shadow-sm">
                                    <p class="text-sm font-semibold text-gray-700 dark:text-gray-200 border-b border-gray-200 dark:border-gray-600 pb-1 mb-2">{{ $monthName }}:</p>
                                    @if(!empty($growthData))
                                        <ul class="space-y-1 text-sm">
                                            @foreach ($growthData as $data)
                                                <li class="flex justify-between items-center">
                                                    <span class="font-medium text-gray-800 dark:text-gray-300">{{ $data['category'] }}</span>
                                                    <span class="font-mono text-xs {{ str_contains($data['growth'], '+') || $data['growth'] === 'Baru' ? 'text-green-600 dark:text-green-400 font-semibold' : (str_contains($data['growth'], '-') ? 'text-red-600 dark:text-red-400 font-semibold' : 'text-gray-600 dark:text-gray-400') }}">
                                                        {{ $data['growth'] }}
                                                    </span>
                                                </li>
                                                <li class="flex justify-between text-xs text-gray-400 dark:text-gray-500 pl-4 mb-2 border-b border-dashed dark:border-gray-700 pb-1">
                                                    <span>(Rp {{ number_format($data['current_value'], 0, ',', '.') }})</span>
                                                    <span>(Rp {{ number_format($data['previous_value'], 0, ',', '.') }})</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <p class="text-xs text-gray-500 dark:text-gray-400 italic">Tidak ada data pertumbuhan untuk bulan ini.</p>
                                    @endif
                                </div>
                            @empty
                                <p class="text-sm text-gray-500 dark:text-gray-400">Data pertumbuhan MoM tidak tersedia untuk periode atau properti yang dipilih.</p>
                            @endforelse
                        @endif
                    </div>
                </div>
            </div>

            {{-- Analisis Pencapaian Target Pendapatan --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-2">Analisis Pencapaian Target Pendapatan</h3>
                 <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">
                    Berdasarkan periode: {{ $filterStartDate->isoFormat('D MMMM YYYY') }} - {{ $filterEndDate->isoFormat('D MMMM YYYY') }} (Total {{ $totalDaysInPeriod }} hari)
                </p>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg shadow">
                        <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Rata-Rata Pencapaian Target</h4>
                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                            {{ isset($targetAnalysis['average_achievement_percentage']) && $targetAnalysis['average_achievement_percentage'] !== null ? number_format($targetAnalysis['average_achievement_percentage'], 2) . '%' : 'N/A' }}
                        </p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg shadow">
                        <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Properti Pencapaian Tertinggi</h4>
                        @if($targetAnalysis['top_property_target'])
                            <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $targetAnalysis['top_property_target']['name'] }}</p>
                            <p class="text-xl font-bold text-green-600 dark:text-green-400">{{ number_format($targetAnalysis['top_property_target']['achievement_percentage'], 2) }}%</p>
                        @else
                            <p class="text-gray-900 dark:text-gray-100">N/A</p>
                        @endif
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg shadow">
                        <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Properti Pencapaian Terendah</h4>
                        @if($targetAnalysis['bottom_property_target'])
                            <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $targetAnalysis['bottom_property_target']['name'] }}</p>
                            <p class="text-xl font-bold text-red-600 dark:text-red-400">{{ number_format($targetAnalysis['bottom_property_target']['achievement_percentage'], 2) }}%</p>
                        @else
                            <p class="text-gray-900 dark:text-gray-100">N/A</p>
                        @endif
                    </div>
                </div>
                @if(isset($targetAnalysis['details']) && count($targetAnalysis['details']) > 0)
                <div class="mt-6 overflow-x-auto">
                    <h4 class="text-md font-medium text-gray-700 dark:text-gray-300 mb-2">Detail Pencapaian per Properti</h4>
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Properti</th>
                                <th scope="col" class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Total Target (Rp)</th>
                                <th scope="col" class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Total Aktual (Rp)</th>
                                <th scope="col" class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Pencapaian (%)</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($targetAnalysis['details'] as $detail)
                            <tr>
                                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $detail['name'] }}</td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm text-right text-gray-500 dark:text-gray-300">{{ number_format($detail['total_target'],0,',','.') }}</td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm text-right text-gray-500 dark:text-gray-300">{{ number_format($detail['total_actual'],0,',','.') }}</td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm text-right font-semibold {{ $detail['achievement_percentage'] === null ? 'text-gray-500 dark:text-gray-400' : ($detail['achievement_percentage'] >= 100 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400') }}">
                                    {{ $detail['achievement_percentage'] !== null ? number_format($detail['achievement_percentage'], 2) . '%' : 'N/A (Target 0)' }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-2">
                    Analisis Pencapaian Harian
                    @if($propertyMomFilterId && $propertyMomFilterId != 'all' && isset($allPropertiesForFilter))
                        <span class="text-base font-medium text-gray-500 dark:text-gray-400">- {{ $allPropertiesForFilter->firstWhere('id', $propertyMomFilterId)->name ?? '' }}</span>
                    @else
                        <span class="text-base font-medium text-gray-500 dark:text-gray-400">- Gabungan Semua Properti</span>
                    @endif
                </h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">
                    Berdasarkan periode: {{ $filterStartDate->isoFormat('D MMMM YYYY') }} - {{ $filterEndDate->isoFormat('D MMMM YYYY') }}.
                    Target harian dihitung pro-rata dari target bulanan.
                </p>

                @if(isset($dailyPerformanceData) && count($dailyPerformanceData) > 0)
                    <div class="mt-4 overflow-y-auto" style="max-height: 500px;">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700 sticky top-0 z-10">
                                <tr>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Tanggal</th>
                                    <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Aktual (Rp)</th>
                                    <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Target (Rp)</th>
                                    <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Pencapaian</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($dailyPerformanceData as $daily)
                                    <tr>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $daily['date']->isoFormat('dddd, D MMM YY') }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-right text-gray-500 dark:text-gray-300 font-mono">{{ number_format($daily['actual_income'], 0, ',', '.') }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-right text-gray-500 dark:text-gray-300 font-mono">{{ number_format($daily['daily_target'], 0, ',', '.') }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-right font-semibold {{ $daily['achievement_percentage'] >= 100 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                            @if($daily['daily_target'] > 0)
                                                {{ number_format($daily['achievement_percentage'], 2, ',', '.') }}%
                                            @else
                                                <span class="text-gray-500 dark:text-gray-400">N/A</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">Tidak ada data performa harian untuk periode atau properti yang dipilih.</p>
                @endif
            </div>
            
        </div>
    </div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const isDarkMode = document.documentElement.classList.contains('dark');
    Chart.defaults.color = isDarkMode ? '#e5e7eb' : '#6b7280';
    Chart.defaults.borderColor = isDarkMode ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)';

    const overallIncomeSourceData = @json($overallIncomeSource ?? null);
    const overallIncomeByPropertyData = @json($overallIncomeByProperty ?? []);
    const trendKontribusiKategoriData = @json($trendKontribusiData ?? ['labels' => [], 'datasets' => []]);
    const categories = @json($categories ?? []);
    
    // 1. Diagram Pie
    const kpiPieChartCanvas = document.getElementById('kpiOverallSourcePieChart');
    if (kpiPieChartCanvas) {
        const pieLabels = Object.values(categories);
        const pieData = overallIncomeSourceData ? Object.keys(categories).map(key => overallIncomeSourceData['total_' + key] || 0) : [];
        const hasData = pieData.some(v => v > 0);

        if (hasData) {
            new Chart(kpiPieChartCanvas, {
                type: 'pie',
                data: {
                    labels: pieLabels,
                    datasets: [{
                        data: pieData,
                        backgroundColor: ['#e6194B', '#3cb44b', '#ffe119', '#4363d8', '#f58231', '#911eb4', '#42d4f4', '#f032e6', '#bfef45', '#808000'],
                        borderWidth: 1
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'top' }, title: { display: false } } }
            });
        } else {
            const ctx = kpiPieChartCanvas.getContext('2d');
            ctx.font = '14px Figtree, sans-serif';
            ctx.fillStyle = isDarkMode ? '#cbd5e1' : '#6b7280';
            ctx.textAlign = 'center';
            ctx.fillText('Tidak ada data distribusi pendapatan.', kpiPieChartCanvas.width / 2, kpiPieChartCanvas.height / 2);
        }
    }

    // 2. Diagram Bar
    const kpiBarChartCanvas = document.getElementById('kpiOverallIncomeByPropertyBarChart');
    if (kpiBarChartCanvas) {
        const hasData = overallIncomeByPropertyData && overallIncomeByPropertyData.length > 0 && overallIncomeByPropertyData.some(p => p.total_revenue > 0);
        if (hasData) {
            new Chart(kpiBarChartCanvas, {
                type: 'bar',
                data: {
                    labels: overallIncomeByPropertyData.map(p => p.name),
                    datasets: [{
                        label: 'Total Pendapatan (Rp)',
                        data: overallIncomeByPropertyData.map(p => p.total_revenue || 0),
                        backgroundColor: 'rgba(54, 162, 235, 0.7)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, ticks: { callback: function(value) { return 'Rp ' + value.toLocaleString('id-ID'); } } } }, plugins: { legend: { display: false }, title: { display: false } } }
            });
        } else if (kpiBarChartCanvas) {
            const ctx = kpiBarChartCanvas.getContext('2d');
            ctx.font = '14px Figtree, sans-serif';
            ctx.fillStyle = isDarkMode ? '#cbd5e1' : '#6b7280';
            ctx.textAlign = 'center';
            ctx.fillText('Tidak ada data pendapatan per properti.', kpiBarChartCanvas.width / 2, kpiBarChartCanvas.height / 2);
        }
    }
    
    // 3. Diagram Line
    const monthlyTrendCanvas = document.getElementById('monthlyCategoryTrendChart');
    if (monthlyTrendCanvas) {
        const hasData = trendKontribusiKategoriData.labels && trendKontribusiKategoriData.labels.length > 0 && trendKontribusiKategoriData.datasets.some(ds => ds.data.some(d => d > 0));
        if (hasData) {
            new Chart(monthlyTrendCanvas, {
                type: 'line',
                data: trendKontribusiKategoriData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true, stacked: false, ticks: { callback: function(value) { return 'Rp ' + value.toLocaleString('id-ID'); } }, title: { display: true, text: 'Total Pendapatan (Rp)' } },
                        x: { title: { display: true, text: 'Periode ({{ $useDailyView ? "Harian" : "Bulanan" }})' } }
                    },
                    plugins: {
                        legend: { position: 'top' },
                        title: { display: false },
                        tooltip: { callbacks: { label: function(context) { let label = context.dataset.label || ''; if (label) { label += ': '; } if (context.parsed.y !== null) { label += 'Rp ' + context.parsed.y.toLocaleString('id-ID'); } return label; } } }
                    }
                }
            });
        } else if (monthlyTrendCanvas) {
            const ctx = monthlyTrendCanvas.getContext('2d');
            ctx.font = '14px Figtree, sans-serif';
            ctx.fillStyle = isDarkMode ? '#cbd5e1' : '#6b7280';
            ctx.textAlign = 'center';
            ctx.fillText('Tidak ada data tren untuk periode/properti ini.', monthlyTrendCanvas.width / 2, monthlyTrendCanvas.height / 2);
        }
    }
});
</script>
@endpush
</x-app-layout>