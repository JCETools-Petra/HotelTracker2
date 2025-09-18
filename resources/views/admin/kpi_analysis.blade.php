<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Pusat Analisis Kinerja (KPI)') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- FORM FILTER --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Filter Analisis</h3>
                    <form action="{{ route('admin.kpi.analysis') }}" method="GET">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div class="md:col-span-2">
                                <label for="property_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Pilih Properti</label>
                                <select name="property_id" id="property_id" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                    <option value="">-- Semua Properti --</option>
                                    @foreach ($properties as $property)
                                        <option value="{{ $property->id }}" {{ $propertyId == $property->id ? 'selected' : '' }}>
                                            {{ $property->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="start_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal Mulai</label>
                                <input type="date" name="start_date" id="start_date" value="{{ $startDate }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-900 dark:border-gray-700 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>
                            <div>
                                <label for="end_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal Selesai</label>
                                <input type="date" name="end_date" id="end_date" value="{{ $endDate }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-900 dark:border-gray-700 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>
                        </div>
                        <div class="mt-4 flex justify-end space-x-2">
                            <a href="#" id="export-excel-btn" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
                                Export Excel
                            </a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                                Terapkan Filter
                            </button>
                        </div>
                        <script>
                            document.getElementById('export-excel-btn').addEventListener('click', function(e) {
                                e.preventDefault();
                                const form = this.closest('form');
                                const propertyId = form.querySelector('[name="property_id"]').value;
                                const startDate = form.querySelector('[name="start_date"]').value;
                                const endDate = form.querySelector('[name="end_date"]').value;
                                
                                const exportUrl = new URL("{{ route('admin.kpi.analysis.export') }}");
                                exportUrl.searchParams.append('property_id', propertyId);
                                exportUrl.searchParams.append('start_date', startDate);
                                exportUrl.searchParams.append('end_date', endDate);
                                
                                window.location.href = exportUrl.toString();
                            });
                        </script>
                    </form>
                </div>
            </div>

            @if ($kpiData)
                {{-- KARTU KPI UTAMA --}}
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm">
                        <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Pendapatan</h4>
                        <p class="mt-1 text-3xl font-semibold text-gray-900 dark:text-gray-100">Rp {{ number_format($kpiData['totalRevenue'], 0, ',', '.') }}</p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm">
                        <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Okupansi Rata-rata</h4>
                        <p class="mt-1 text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ number_format($kpiData['avgOccupancy'], 2) }}%</p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm">
                        <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Average Room Rate (ARR)</h4>
                        <p class="mt-1 text-3xl font-semibold text-gray-900 dark:text-gray-100">Rp {{ number_format($kpiData['avgArr'], 0, ',', '.') }}</p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm">
                        <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Revenue Per Available Room (RevPAR)</h4>
                        <p class="mt-1 text-3xl font-semibold text-gray-900 dark:text-gray-100">Rp {{ number_format($kpiData['revPar'], 0, ',', '.') }}</p>
                    </div>
                </div>

                {{-- GRAFIK KINERJA HARIAN --}}
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Grafik Kinerja Harian</h3>
                        <canvas id="kpiChart"></canvas>
                    </div>
                </div>

                {{-- KARTU RINCIAN --}}
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm">
                        <h4 class="text-base font-medium text-gray-900 dark:text-gray-100 mb-4">Rincian Pendapatan Kamar</h4>
                        <ul class="space-y-2">
                            @foreach($kpiData['revenueBreakdown'] as $source => $amount)
                                <li class="flex justify-between text-sm">
                                    <span class="text-gray-600 dark:text-gray-400">{{ $source }}</span>
                                    <span class="font-semibold text-gray-800 dark:text-gray-200">Rp {{ number_format($amount, 0, ',', '.') }}</span>
                                </li>
                            @endforeach
                             <li class="flex justify-between text-sm font-bold border-t pt-2 mt-2">
                                <span class="text-gray-800 dark:text-gray-200">Total</span>
                                <span class="text-gray-800 dark:text-gray-200">Rp {{ number_format($kpiData['totalRoomRevenue'], 0, ',', '.') }}</span>
                            </li>
                        </ul>
                    </div>
                     <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm">
                        <h4 class="text-base font-medium text-gray-900 dark:text-gray-100 mb-4">Pendapatan Lainnya</h4>
                         <ul class="space-y-2">
                            <li class="flex justify-between text-sm">
                                <span class="text-gray-600 dark:text-gray-400">Food & Beverage</span>
                                <span class="font-semibold text-gray-800 dark:text-gray-200">Rp {{ number_format($kpiData['totalFbRevenue'], 0, ',', '.') }}</span>
                            </li>
                            <li class="flex justify-between text-sm">
                                <span class="text-gray-600 dark:text-gray-400">Lain-lain</span>
                                <span class="font-semibold text-gray-800 dark:text-gray-200">Rp {{ number_format($kpiData['totalOtherRevenue'], 0, ',', '.') }}</span>
                            </li>
                        </ul>
                    </div>
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm">
                        <h4 class="text-base font-medium text-gray-900 dark:text-gray-100 mb-4">Rincian Kamar Terjual</h4>
                        <ul class="space-y-2">
                             @foreach($kpiData['roomsSoldBreakdown'] as $source => $qty)
                                <li class="flex justify-between text-sm">
                                    <span class="text-gray-600 dark:text-gray-400">{{ $source }}</span>
                                    <span class="font-semibold text-gray-800 dark:text-gray-200">{{ number_format($qty) }}</span>
                                </li>
                            @endforeach
                            <li class="flex justify-between text-sm font-bold border-t pt-2 mt-2">
                                <span class="text-gray-800 dark:text-gray-200">Total Kamar Terjual</span>
                                <span class="text-gray-800 dark:text-gray-200">{{ number_format($kpiData['totalRoomsSold']) }}</span>
                            </li>
                        </ul>
                    </div>
                </div>

                {{-- TABEL RINCIAN HARIAN --}}
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Tabel Rincian Harian</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Tanggal</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Pendapatan</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Okupansi (%)</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">ARR</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Kamar Terjual</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($dailyData as $data)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $data['date'] }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Rp {{ number_format($data['revenue'], 0, ',', '.') }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $data['occupancy'] }}%</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Rp {{ number_format($data['arr'], 0, ',', '.') }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $data['rooms_sold'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            @elseif($propertyId)
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                     <div class="p-6 text-gray-900 dark:text-gray-100 text-center">
                        Tidak ada data ditemukan untuk properti dan rentang tanggal yang dipilih.
                    </div>
                </div>
            @else
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100 text-center">
                        Silakan pilih properti untuk memulai analisis.
                    </div>
                </div>
            @endif
        </div>
    </div>
    
    {{-- SCRIPT UNTUK CHART.JS --}}
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            @if($kpiData && $dailyData->isNotEmpty())
                const dailyData = @json($dailyData);
                
                const labels = dailyData.map(item => item.date);
                const revenueData = dailyData.map(item => item.revenue);
                const occupancyData = dailyData.map(item => item.occupancy);
                const arrData = dailyData.map(item => item.arr);

                const ctx = document.getElementById('kpiChart').getContext('2d');
                const kpiChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                label: 'Pendapatan Harian',
                                data: revenueData,
                                borderColor: 'rgba(54, 162, 235, 1)',
                                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                                yAxisID: 'y',
                            },
                            {
                                label: 'Okupansi Harian (%)',
                                data: occupancyData,
                                borderColor: 'rgba(255, 99, 132, 1)',
                                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                                yAxisID: 'y1',
                            }
                        ]
                    },
                    options: {
                        scales: {
                            y: {
                                type: 'linear',
                                display: true,
                                position: 'left',
                                ticks: {
                                    callback: function(value, index, values) {
                                        return 'Rp ' + new Intl.NumberFormat().format(value);
                                    }
                                }
                            },
                            y1: {
                                type: 'linear',
                                display: true,
                                position: 'right',
                                grid: {
                                    drawOnChartArea: false, // only want the grid lines for one axis to show up
                                },
                                ticks: {
                                     callback: function(value, index, values) {
                                        return value + '%';
                                    }
                                }
                            }
                        }
                    }
                });
            @endif
        </script>
    @endpush

</x-admin-layout>