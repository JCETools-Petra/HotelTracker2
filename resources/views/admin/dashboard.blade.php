<x-app-layout>
    @php
        $chartColors = ['#e6194B', '#3cb44b', '#ffe119', '#4363d8', '#f58231', '#911eb4', '#42d4f4', '#f032e6', '#bfef45', '#808000', '#000075', '#a9a9a9'];
        
        $revenueTitle = 'Ringkasan Pendapatan';
        if ($period === 'today') {
            $revenueTitle = 'Daily Revenue';
        } elseif ($period === 'month') {
            $revenueTitle = 'Monthly Revenue';
        } elseif ($period === 'year') {
            $revenueTitle = 'Yearly Revenue';
        } elseif ($period === 'custom') {
            if ($startDate->isSameDay($endDate)) {
                $revenueTitle = 'Revenue ' . $startDate->isoFormat('D MMMM YYYY');
            } elseif ($startDate->day == 1 && $endDate->day == $endDate->daysInMonth) {
                $revenueTitle = $startDate->isoFormat('MMMM YYYY') . ' Revenue';
            } else {
                $revenueTitle = 'Periode Revenue (' . $startDate->isoFormat('D MMM YY') . ' - ' . $endDate->isoFormat('D MMM YY') . ')';
            }
        }
    @endphp

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Admin Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="mb-6 p-4 bg-white dark:bg-gray-800 rounded-lg shadow-sm">
                <form action="{{ route('admin.dashboard') }}" method="GET" id="filter-form" class="space-y-4">
                    <input type="hidden" name="property_id" id="property_id_hidden">
                    <input type="hidden" name="start_date" id="start_date_hidden">
                    <input type="hidden" name="end_date" id="end_date_hidden">
                    <input type="hidden" name="period" id="period_hidden">

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                        <div class="lg:col-span-2">
                            <label for="property_select" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Pilih Properti</label>
                            <select id="property_select" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md shadow-sm">
                                <option value="">Semua Properti</option>
                                @foreach($allPropertiesForFilter as $property)
                                    <option value="{{ $property->id }}" @selected($propertyId == $property->id)>{{ $property->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="lg:col-span-3 flex items-end">
                            <div class="w-full">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Pilih Periode Cepat</label>
                                <div class="mt-1 flex rounded-md shadow-sm filter-button-group">
                                    <button type="button" data-period="today" class="filter-button quick-filter-btn rounded-l-md {{ $period == 'today' ? 'active' : '' }}">Hari Ini</button>
                                    <button type="button" data-period="month" class="filter-button quick-filter-btn -ml-px {{ $period == 'month' ? 'active' : '' }}">Bulan Ini</button>
                                    <button type="button" data-period="year" class="filter-button quick-filter-btn -ml-px {{ $period == 'year' ? 'active' : '' }}">Tahun Ini</button>
                                    <a href="{{ route('admin.dashboard') }}" class="filter-button -ml-px rounded-r-md">Reset</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="pt-4 border-t dark:border-gray-700">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Atau Pilih Periode Kustom:</label>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
                            <div>
                                <label for="year_select" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tahun</label>
                                <select id="year_select" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md shadow-sm">
                                    @for ($y = now()->year + 1; $y >= now()->year - 5; $y--)
                                        <option value="{{ $y }}" @selected($startDate->year == $y)>{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div>
                                <label for="month_select" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Bulan</label>
                                <select id="month_select" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md shadow-sm">
                                    @for ($m = 1; $m <= 12; $m++)
                                        <option value="{{ $m }}" @selected($startDate->month == $m)>{{ \Carbon\Carbon::create(null, $m)->isoFormat('MMMM') }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div>
                                <label for="day_select" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal</label>
                                <select id="day_select" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md shadow-sm"></select>
                            </div>
                            <div class="flex items-center h-10">
                                <label for="full_month_checkbox" class="flex items-center space-x-2 cursor-pointer">
                                    <input type="checkbox" id="full_month_checkbox" class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm"
                                           @if($period === 'custom' && $startDate->day == 1 && $endDate->day == $endDate->daysInMonth) checked @endif>
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Lihat Sebulan Penuh</span>
                                </label>
                            </div>
                            <div>
                                 <button type="button" id="apply_custom_filter" class="w-full inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">Terapkan</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            
            <div class="mb-6 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="font-semibold text-lg text-gray-600 dark:text-gray-300">Total Pendapatan (Periode Terfilter)</h3>
                    <p class="text-4xl font-bold text-green-600 dark:text-green-400 mt-2">
                        Rp {{ number_format($totalOverallRevenue ?? 0, 0, ',', '.') }}
                    </p>
                </div>
            </div>
            
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">{{ $revenueTitle }}</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <div class="p-4 border dark:border-gray-700 rounded-lg">
                        <h4 class="text-md font-medium text-gray-700 dark:text-gray-300 mb-2">Distribusi Sumber Pendapatan</h4>
                        <div id="pieChartContainer" class="flex flex-col md:flex-row items-center gap-4" style="min-height: 300px;">
                            <div class="w-full md:w-1/2"><canvas id="overallSourcePieChart"></canvas></div>
                            <div class="w-full md:w-1/2 space-y-1" id="pieChartLegend"></div>
                        </div>
                    </div>
                    <div class="p-4 border dark:border-gray-700 rounded-lg">
                        <h4 class="text-md font-medium text-gray-700 dark:text-gray-300 mb-2">Total Pendapatan per Properti</h4>
                        <div id="barChartContainer" style="height: 300px;"><canvas id="overallIncomeByPropertyBarChart"></canvas></div>
                    </div>
                </div>
                
                <div class="flex flex-wrap justify-between items-center mt-8 mb-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Detail Properti</h3>
                    @if(!$properties->isEmpty())
                    <div class="flex space-x-2">
                        <a href="{{ route('admin.dashboard.export.excel', request()->query()) }}" class="inline-flex items-center px-3 py-1.5 bg-green-600 hover:bg-green-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest">Export Excel</a>
                        <a href="{{ route('admin.dashboard.export.csv', request()->query()) }}" class="inline-flex items-center px-3 py-1.5 bg-blue-600 hover:bg-blue-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest">Export CSV</a>
                    </div>
                    @endif
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @forelse($properties as $property)
                        <div class="bg-gray-50 dark:bg-gray-900/50 p-6 rounded-lg shadow-sm">
                            @include('admin.properties._property_card', ['property' => $property, 'incomeCategories' => $incomeCategories, 'revenueTitle' => $revenueTitle])
                        </div>
                    @empty
                        <div class="col-span-full text-center py-8">
                            <p class="text-gray-600 dark:text-gray-400">Tidak ada data properti yang ditemukan.</p>
                        </div>
                    @endforelse
                </div>

                <div class="mt-8">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Laporan Event MICE</h3>
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-0">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700/50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Nama Pemesan</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Hotel</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Kategori</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Tanggal</th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Nilai</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @forelse ($recentMiceBookings as $event)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">{{ $event->client_name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $event->property->name ?? 'N/A' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400"><span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-100">{{ $event->miceCategory->name ?? 'N/A' }}</span></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ \Carbon\Carbon::parse($event->event_date)->format('d M Y') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 dark:text-gray-200 text-right">Rp {{ number_format($event->total_price, 0, ',', '.') }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">Tidak ada data event MICE pada periode ini.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
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
                const form = document.getElementById('filter-form');
                const yearSelect = document.getElementById('year_select');
                const monthSelect = document.getElementById('month_select');
                const daySelect = document.getElementById('day_select');
                const fullMonthCheckbox = document.getElementById('full_month_checkbox');
                const propertySelect = document.getElementById('property_select');
                
                const startDateHidden = document.getElementById('start_date_hidden');
                const endDateHidden = document.getElementById('end_date_hidden');
                const propertyIdHidden = document.getElementById('property_id_hidden');
                const periodHidden = document.getElementById('period_hidden');

                const populateDays = () => {
                    const year = yearSelect.value;
                    const month = monthSelect.value;
                    const daysInMonth = new Date(year, month, 0).getDate();
                    
                    const currentDay = '{{ $period === "custom" ? $startDate->day : now()->day }}';
                    let selectedDay = daySelect.value || currentDay;
                    if (selectedDay > daysInMonth) {
                        selectedDay = daysInMonth;
                    }
                    
                    daySelect.innerHTML = '';
                    for (let i = 1; i <= daysInMonth; i++) {
                        const option = document.createElement('option');
                        option.value = i;
                        option.text = i;
                        if (i == selectedDay) {
                            option.selected = true;
                        }
                        daySelect.appendChild(option);
                    }
                };

                const toggleDaySelect = () => {
                    daySelect.disabled = fullMonthCheckbox.checked;
                    daySelect.classList.toggle('bg-gray-200', fullMonthCheckbox.checked);
                    daySelect.classList.toggle('dark:bg-gray-800', fullMonthCheckbox.checked);
                };

                const submitForm = () => {
                    propertyIdHidden.value = propertySelect.value;
                    form.submit();
                };

                document.querySelectorAll('.quick-filter-btn').forEach(button => {
                    button.addEventListener('click', function() {
                        periodHidden.value = this.dataset.period;
                        startDateHidden.value = '';
                        endDateHidden.value = '';
                        submitForm();
                    });
                });

                document.getElementById('apply_custom_filter').addEventListener('click', function() {
                    const year = yearSelect.value;
                    const month = monthSelect.value.toString().padStart(2, '0');
                    let startDateStr, endDateStr;

                    if (fullMonthCheckbox.checked) {
                        startDateStr = `${year}-${month}-01`;
                        const lastDay = new Date(year, month, 0).getDate();
                        endDateStr = `${year}-${month}-${String(lastDay).padStart(2, '0')}`;
                    } else {
                        const day = daySelect.value.toString().padStart(2, '0');
                        startDateStr = `${year}-${month}-${day}`;
                        endDateStr = startDateStr;
                    }
                    
                    startDateHidden.value = startDateStr;
                    endDateHidden.value = endDateStr;
                    periodHidden.value = 'custom';
                    
                    submitForm();
                });

                yearSelect.addEventListener('change', populateDays);
                monthSelect.addEventListener('change', populateDays);
                fullMonthCheckbox.addEventListener('change', toggleDaySelect);
                
                populateDays();
                toggleDaySelect();
                
                const isDarkMode = document.documentElement.classList.contains('dark');
                Chart.defaults.color = isDarkMode ? '#e5e7eb' : '#6b7280';
                Chart.defaults.borderColor = isDarkMode ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)';
                const pieChartData = @json($pieChartDataSource);
                const pieChartCategories = @json($pieChartCategories);
                const overallIncomeByPropertyData = @json($overallIncomeByProperty);
                const chartColors = @json($chartColors);
                let myPieChart;
                const pieCanvas = document.getElementById('overallSourcePieChart');
                const pieLegendContainer = document.getElementById('pieChartLegend');
                const pieChartContainer = document.getElementById('pieChartContainer');
                if (pieCanvas && pieLegendContainer && pieChartContainer) {
                    const pieLabels = Object.values(pieChartCategories);
                    const pieDataValues = pieChartData ? Object.keys(pieChartCategories).map(key => pieChartData['total_' + key] || 0) : [];
                    const hasPieData = pieDataValues.some(v => v > 0);
                    if (hasPieData) {
                        myPieChart = new Chart(pieCanvas, {type: 'pie',data: { labels: pieLabels, datasets: [{ data: pieDataValues, backgroundColor: chartColors }] },options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }});
                        pieLegendContainer.innerHTML = '';
                        pieLabels.forEach((label, index) => {
                            const value = pieDataValues[index];
                            if (value > 0) {
                                const legendItem = document.createElement('div');
                                legendItem.classList.add('flex', 'items-center', 'p-1', 'rounded', 'cursor-pointer', 'hover:bg-gray-100', 'dark:hover:bg-gray-700');
                                legendItem.dataset.index = index;
                                legendItem.innerHTML = `<span class="w-3 h-3 rounded-full mr-2 flex-shrink-0" style="background-color: ${chartColors[index % chartColors.length]};"></span><div class="flex justify-between items-center w-full text-xs"><span class="legend-label text-gray-600 dark:text-gray-400 mr-2 truncate" title="${label}">${label}</span><span class="font-semibold text-gray-800 dark:text-gray-200 text-right whitespace-nowrap">Rp ${new Intl.NumberFormat('id-ID').format(value)}</span></div>`;
                                legendItem.addEventListener('click', (event) => {
                                    const clickedIndex = parseInt(event.currentTarget.dataset.index);
                                    myPieChart.toggleDataVisibility(clickedIndex);
                                    myPieChart.update();
                                    event.currentTarget.classList.toggle('opacity-50');
                                    event.currentTarget.querySelector('.legend-label').classList.toggle('line-through');
                                });
                                pieLegendContainer.appendChild(legendItem);
                            }
                        });
                    } else {
                        pieChartContainer.innerHTML = `<div class="flex items-center justify-center w-full h-full text-gray-500 dark:text-gray-400">Tidak ada data untuk filter ini.</div>`;
                    }
                }
                const barCanvas = document.getElementById('overallIncomeByPropertyBarChart');
                if (barCanvas) {
                    const hasBarData = overallIncomeByPropertyData && overallIncomeByPropertyData.some(p => p.total_revenue > 0);
                    if (hasBarData) {
                        const propertyColors = overallIncomeByPropertyData.map(p => p.chart_color || '#36A2EB');
                        new Chart(barCanvas, {type: 'bar',data: {labels: overallIncomeByPropertyData.map(p => p.name),datasets: [{label: 'Total Pendapatan (Rp)',data: overallIncomeByPropertyData.map(p => p.total_revenue || 0),backgroundColor: propertyColors,}]},options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true } }, plugins: { legend: { display: false } } }});
                    } else {
                        const barContainer = document.getElementById('barChartContainer');
                        barContainer.innerHTML = `<div class="flex items-center justify-center h-full text-gray-500 dark:text-gray-400">Tidak ada data pendapatan pada periode ini.</div>`;
                    }
                }
            });
        </script>
    @endpush
</x-app-layout>