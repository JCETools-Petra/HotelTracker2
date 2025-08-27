<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Kalender Terpusat') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div id="chart-container" class="mb-8 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex flex-wrap justify-between items-center mb-4 gap-4">
                        <h3 class="font-semibold text-lg text-gray-800 dark:text-gray-200">Grafik Okupansi Ecommerce (30 Hari Terakhir)</h3>
                        <div>
                            <select id="property-filter" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300">
                                <option value="all">Semua Properti</option>
                                @foreach($properties as $property)
                                    <option value="{{ $property->id }}">{{ $property->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="h-80">
                        <canvas id="occupancyChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="flex flex-wrap justify-between items-center mb-4 gap-4">
                        <h3 class="text-lg font-medium">Tampilan Kalender</h3>
                        <div class="flex space-x-2">
                            <button id="btn-ecommerce" data-source="ecommerce" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md shadow-sm">Reservasi Ecommerce</button>
                            <button id="btn-sales" data-source="sales" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 dark:text-gray-200 dark:bg-gray-600 rounded-md">Booking Sales</button>
                        </div>
                    </div>
                    <div id="unified-calendar"></div>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
        <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet" />
    @endpush

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const calendarEl = document.getElementById('unified-calendar');
                const btnEcommerce = document.getElementById('btn-ecommerce');
                const btnSales = document.getElementById('btn-sales');
                const chartContainer = document.getElementById('chart-container');
                const propertyFilter = document.getElementById('property-filter');
                const ctx = document.getElementById('occupancyChart').getContext('2d');
                let occupancyChart;
                let currentSource = 'ecommerce';

                const calendar = new FullCalendar.Calendar(calendarEl, {
                    initialView: 'dayGridMonth',
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth,timeGridWeek,listWeek'
                    },
                });
                
                function renderChart(chartData) {
                    if (occupancyChart) {
                        occupancyChart.data.labels = chartData.labels;
                        occupancyChart.data.datasets[0].data = chartData.data;
                        occupancyChart.update();
                    } else {
                        occupancyChart = new Chart(ctx, {
                            type: 'line',
                            data: {
                                labels: chartData.labels,
                                datasets: [{
                                    label: 'Total Kamar Terisi',
                                    data: chartData.data,
                                    borderColor: 'rgba(79, 70, 229, 1)',
                                    backgroundColor: 'rgba(79, 70, 229, 0.1)',
                                    fill: true,
                                    tension: 0.3
                                }]
                            },
                            options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true } } }
                        });
                    }
                }

                function fetchData() {
                    const propertyId = propertyFilter.value;
                    
                    if (currentSource === 'ecommerce') {
                        chartContainer.style.display = 'block';
                    } else {
                        chartContainer.style.display = 'none';
                    }
                    
                    fetch(`{{ route('admin.calendar.unified.events') }}?source=${currentSource}&property_id=${propertyId}`)
                        .then(response => response.json())
                        .then(data => {
                            calendar.removeAllEvents();
                            calendar.addEventSource(data.events);
                            
                            if (currentSource === 'ecommerce' && data.chartData) {
                                renderChart(data.chartData);
                            }
                        });
                }

                function setActiveButton(source) {
                    currentSource = source; // Update current source
                    const isEcommerce = source === 'ecommerce';
                    btnEcommerce.classList.toggle('bg-indigo-600', isEcommerce);
                    btnEcommerce.classList.toggle('text-white', isEcommerce);
                    btnEcommerce.classList.toggle('bg-gray-200', !isEcommerce);
                    btnEcommerce.classList.toggle('text-gray-700', !isEcommerce);
                    
                    btnSales.classList.toggle('bg-indigo-600', !isEcommerce);
                    btnSales.classList.toggle('text-white', !isEcommerce);
                    btnSales.classList.toggle('bg-gray-200', isEcommerce);
                    btnSales.classList.toggle('text-gray-700', isEcommerce);
                }

                btnEcommerce.addEventListener('click', function() {
                    setActiveButton('ecommerce');
                    fetchData();
                });

                btnSales.addEventListener('click', function() {
                    setActiveButton('sales');
                    fetchData();
                });

                propertyFilter.addEventListener('change', function() {
                    fetchData();
                });
                
                calendar.render();
                fetchData(); // Muat data awal
            });
        </script>
    @endpush
</x-admin-layout>