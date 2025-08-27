<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Kalender Reservasi') }}
            </h2>
            <a href="{{ route('ecommerce.reservations.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                + Reservasi Baru
            </a>
        </div>
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
                    <div class="h-80 relative"><canvas id="occupancyChart"></canvas></div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div id="calendar"></div>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
        <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet" />
    @endpush

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Daftarkan plugin datalabels secara global
                Chart.register(ChartDataLabels);

                // Inisialisasi elemen DOM
                const calendarEl = document.getElementById('calendar');
                const propertyFilter = document.getElementById('property-filter');
                const btnMonth = document.getElementById('btn-month');
                const btnYear = document.getElementById('btn-year');
                const ctx = document.getElementById('occupancyChart').getContext('2d');
                let occupancyChart;
                let currentRange = 'month';

                // Inisialisasi kalender
                const calendar = new FullCalendar.Calendar(calendarEl, {
                    initialView: 'dayGridMonth',
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth,timeGridWeek,listWeek'
                    },
                    eventClick: function(info) {
                        window.location.href = `/ecommerce/reservations/${info.event.id}/edit`;
                    }
                });

                // Fungsi untuk merender atau mengupdate chart
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
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                scales: {
                                    y: {
                                        beginAtZero: true
                                    }
                                },
                                plugins: {
                                    datalabels: {
                                        anchor: 'end',
                                        align: 'end',
                                        color: '#555',
                                        font: {
                                            weight: 'bold',
                                        },
                                        formatter: function(value) {
                                            return value > 0 ? value : '';
                                        },
                                        backgroundColor: 'rgba(255, 255, 255, 0.7)',
                                        borderRadius: 4,
                                        padding: 4
                                    }
                                }
                            }
                        });
                    }
                }

                // Fungsi untuk mengambil data dari server
                function fetchData() {
                    const propertyId = propertyFilter.value;
                    const range = currentRange;
                    
                    fetch(`{{ route('ecommerce.dashboard.calendar') }}?range=${range}&property_id=${propertyId}`)
                        .then(response => response.json())
                        .then(data => {
                            calendar.removeAllEvents();
                            calendar.addEventSource(data.events);
                            
                            if (data.chartData) {
                                renderChart(data.chartData);
                            }
                        })
                        .catch(error => {
                            console.error('Error fetching data:', error);
                        });
                }

                // Fungsi untuk mengatur tombol aktif
                function setActiveButton(range) {
                    currentRange = range;
                    const isMonth = range === 'month';

                    btnMonth.classList.toggle('bg-indigo-600', isMonth);
                    btnMonth.classList.toggle('text-white', isMonth);
                    btnMonth.classList.toggle('bg-gray-200', !isMonth);
                    btnMonth.classList.toggle('dark:bg-gray-600', !isMonth);
                    btnMonth.classList.toggle('text-gray-700', !isMonth);

                    btnYear.classList.toggle('bg-indigo-600', !isMonth);
                    btnYear.classList.toggle('text-white', !isMonth);
                    btnYear.classList.toggle('bg-gray-200', isMonth);
                    btnYear.classList.toggle('dark:bg-gray-600', isMonth);
                    btnYear.classList.toggle('text-gray-700', isMonth);
                }

                // Tambahkan event listener ke tombol dan filter
                btnMonth.addEventListener('click', function() {
                    setActiveButton('month');
                    fetchData();
                });

                btnYear.addEventListener('click', function() {
                    setActiveButton('year');
                    fetchData();
                });

                propertyFilter.addEventListener('change', fetchData);
                
                // Render kalender dan ambil data awal
                calendar.render();
                fetchData();
            });
        </script>
    @endpush
</x-app-layout>