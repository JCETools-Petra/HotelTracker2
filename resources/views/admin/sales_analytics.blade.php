<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Analisis Sales & Event') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md">
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Pendapatan Event</h3>
                    <p class="mt-2 text-3xl font-bold text-green-600 dark:text-green-500">Rp {{ number_format($totalEventRevenue, 0, ',', '.') }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md">
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Booking</h3>
                    <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $totalBookings }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md">
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Booking Dikonfirmasi</h3>
                    <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $totalConfirmedBookings }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md">
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Paket Harga Aktif</h3>
                    <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $totalActivePackages }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 mb-8">
                <div class="lg:col-span-3 bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Pendapatan Event (12 Bulan Terakhir)</h3>
                    <div>
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>
                <div class="lg:col-span-2 bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Distribusi Status Booking</h3>
                    <div class="max-w-xs mx-auto">
                        <canvas id="bookingStatusChart"></canvas>
                    </div>
                </div>
            </div>

        </div>
    </div>
    
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Data dari Controller
            const revenueChartData = @json($revenueChartData);
            const bookingStatusData = @json($pieChartData);

            // Grafik Batang: Pendapatan Event
            if(document.getElementById('revenueChart')) {
                const revenueCtx = document.getElementById('revenueChart').getContext('2d');
                new Chart(revenueCtx, {
                    type: 'bar',
                    data: {
                        labels: revenueChartData.labels,
                        datasets: [{
                            label: 'Total Pendapatan',
                            data: revenueChartData.data,
                            backgroundColor: 'rgba(54, 162, 235, 0.6)',
                            borderColor: 'rgba(54, 162, 235, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value, index, values) {
                                        return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // Grafik Pie: Status Booking
            if(document.getElementById('bookingStatusChart')) {
                const bookingStatusCtx = document.getElementById('bookingStatusChart').getContext('2d');
                new Chart(bookingStatusCtx, {
                    type: 'pie',
                    data: {
                        labels: bookingStatusData.labels,
                        datasets: [{
                            label: 'Jumlah Booking',
                            data: bookingStatusData.data,
                            backgroundColor: [
                                'rgba(22, 163, 74, 0.7)',  // Green
                                'rgba(245, 158, 11, 0.7)', // Yellow
                                'rgba(239, 68, 68, 0.7)'    // Red
                            ],
                            hoverOffset: 4
                        }]
                    },
                });
            }
        });
    </script>
    @endpush
</x-app-layout>