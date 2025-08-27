<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Kalender Reservasi') }}
            </h2>
            <a href="{{ route('property.reservations.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                + Reservasi Baru
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-8 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="font-semibold text-lg text-gray-800 dark:text-gray-200 mb-4">Grafik Okupansi (30 Hari Terakhir)</h3>
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
                // Daftarkan plugin chart
                Chart.register(ChartDataLabels);

                const calendarEl = document.getElementById('calendar');
                const ctx = document.getElementById('occupancyChart').getContext('2d');
                let occupancyChart;

                // Inisialisasi FullCalendar
                const calendar = new FullCalendar.Calendar(calendarEl, {
                    initialView: 'dayGridMonth',
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth,timeGridWeek,listWeek'
                    },
                    eventClick: function(info) {
                        // Arahkan ke halaman edit reservasi di dalam scope 'property'
                        window.location.href = `/property/reservations/${info.event.id}/edit`;
                    }
                });
                
                // Fungsi untuk merender chart
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
                                    label: 'Kamar Terisi',
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
                                scales: { y: { beginAtZero: true } },
                                plugins: {
                                    datalabels: {
                                        anchor: 'end', align: 'end', color: '#555',
                                        font: { weight: 'bold' },
                                        formatter: (value) => value > 0 ? value : '',
                                        backgroundColor: 'rgba(255, 255, 255, 0.7)',
                                        borderRadius: 4, padding: 4
                                    }
                                }
                            }
                        });
                    }
                }
                
                // Fungsi untuk mengambil semua data (event kalender dan data chart)
                function fetchData() {
                    // Gunakan route yang benar untuk 'pengguna_properti'
                    fetch("{{ route('property.calendar.data') }}")
                        .then(response => response.json())
                        .then(data => {
                            calendar.removeAllEvents();
                            calendar.addEventSource(data.events);
                            
                            if (data.chartData) {
                                renderChart(data.chartData);
                            }
                        })
                        .catch(error => console.error('Error fetching data:', error));
                }

                // Render kalender dan ambil data awal saat halaman dimuat
                calendar.render();
                fetchData();
            });
        </script>
    @endpush
</x-app-layout>