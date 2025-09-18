<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Hasil Perbandingan Properti') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-screen-xl mx-auto sm:px-6 lg:px-8">
            {{-- HEADER HALAMAN --}}
            <div class="mb-6 px-4 sm:px-0">
                <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Perbandingan Kinerja Properti</h3>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Menampilkan hasil perbandingan untuk periode {{ \Carbon\Carbon::parse($startDate)->isoFormat('D MMMM YYYY') }} - {{ \Carbon\Carbon::parse($endDate)->isoFormat('D MMMM YYYY') }}.
                </p>
            </div>

            {{-- KARTU RINGKASAN UTAMA --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-{{ count($properties) > 4 ? 4 : count($properties) }} gap-6 mb-8">
                @foreach($properties as $property)
                    @php $result = $results->get($property->id); @endphp
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center space-x-3">
                                <div class="p-2 rounded-full" style="background-color: {{ $property->chart_color ?? '#e2e8f0' }}20;">
                                    <svg class="w-6 h-6" style="color: {{ $property->chart_color ?? '#94a3b8' }};" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                </div>
                                <h3 class="font-bold text-lg text-gray-900 dark:text-gray-100">{{ $property->name }}</h3>
                            </div>
                            <div class="mt-4">
                                <p class="text-sm text-gray-500 dark:text-gray-400">Total Pendapatan</p>
                                <p class="text-3xl font-bold text-gray-900 dark:text-white">Rp {{ number_format($result->total_overall_revenue ?? 0, 0, ',', '.') }}</p>
                            </div>
                        </div>
                        <div class="mt-6 border-t dark:border-gray-700 pt-4 space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500 dark:text-gray-400">Okupansi Rata-rata</span>
                                <span class="font-semibold text-gray-700 dark:text-gray-300">{{ number_format($result->average_occupancy ?? 0, 2) }}%</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500 dark:text-gray-400">Average Room Rate</span>
                                <span class="font-semibold text-gray-700 dark:text-gray-300">Rp {{ number_format($result->average_arr ?? 0, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            
            {{-- ======================= AWAL PERBAIKAN GRAFIK ======================= --}}
            <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 mb-8">
                <div class="lg:col-span-3 bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 flex flex-col">
                    <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Grafik Perbandingan Pendapatan</h4>
                    {{-- Bungkus canvas dengan div yang memiliki posisi relative dan tinggi pasti --}}
                    <div class="relative mt-4 h-96">
                        <canvas id="comparisonBarChart"></canvas>
                    </div>
                </div>
                <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 flex flex-col">
                    <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Kontribusi Pendapatan</h4>
                     {{-- Bungkus canvas dengan div yang memiliki posisi relative dan tinggi pasti --}}
                    <div class="relative mt-4 h-96">
                        <canvas id="comparisonPieChart"></canvas>
                    </div>
                </div>
            </div>
            {{-- ======================= AKHIR PERBAIKAN GRAFIK ======================= --}}


            {{-- TABEL PERBANDINGAN DETAIL --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-xl font-semibold mb-4">Rincian Metrik Perbandingan</h3>
                    <div class="overflow-x-auto">
                        {{-- Tabel di sini tidak perlu diubah --}}
                        <table class="min-w-full">
                            {{-- ... isi tabel lengkap Anda dari sebelumnya ... --}}
                            <thead class="border-b-2 border-gray-200 dark:border-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Metrik
                                    </th>
                                    @foreach($properties as $property)
                                        <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            {{ $property->name }}
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                <tr class="bg-gray-50 dark:bg-gray-900/50">
                                    <td colspan="{{ count($properties) + 1 }}" class="px-6 py-2 text-sm font-semibold text-gray-600 dark:text-gray-300">Pendapatan Kamar</td>
                                </tr>
                                @php
                                    $metrics = [
                                        'offline' => 'Offline', 'online' => 'Online', 'ta' => 'Travel Agent',
                                        'gov' => 'Government', 'corp' => 'Corporate', 'afiliasi' => 'Afiliasi',
                                    ];
                                @endphp
                                @foreach($metrics as $key => $label)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-800 dark:text-gray-200">{{ $label }}</td>
                                    @foreach($properties as $property)
                                        @php
                                            $result = $results->get($property->id);
                                            $revenue = $result ? $result->{$key.'_revenue'} : 0;
                                            $rooms = $result ? $result->{$key.'_rooms'} : 0;
                                        @endphp
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300 text-center">
                                            <div class="font-semibold">Rp {{ number_format($revenue, 0, ',', '.') }}</div>
                                            @if($rooms > 0)
                                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">({{ $rooms }} kamar)</div>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                                @endforeach
                                <tr class="bg-gray-100 dark:bg-gray-700/50 font-bold">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">Subtotal Pendapatan Kamar</td>
                                    @foreach($properties as $property)
                                        @php
                                            $result = $results->get($property->id);
                                            $revenue = $result ? $result->total_room_revenue : 0;
                                            $rooms = $result ? $result->total_rooms_sold : 0;
                                        @endphp
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 text-center">
                                            <div>Rp {{ number_format($revenue, 0, ',', '.') }}</div>
                                            <div class="text-xs font-normal mt-1">({{ $rooms }} kamar)</div>
                                        </td>
                                    @endforeach
                                </tr>
                                <tr class="bg-gray-50 dark:bg-gray-900/50">
                                    <td colspan="{{ count($properties) + 1 }}" class="px-6 py-2 text-sm font-semibold text-gray-600 dark:text-gray-300">Pendapatan Lainnya</td>
                                </tr>
                                <tr><td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-800 dark:text-gray-200">Food & Beverage</td>@foreach($properties as $property) <td class="px-6 py-4 text-center text-sm">Rp {{ number_format($results->get($property->id)->total_fb_revenue ?? 0, 0, ',', '.') }}</td> @endforeach </tr>
                                <tr><td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-800 dark:text-gray-200">MICE/Event</td>@foreach($properties as $property) <td class="px-6 py-4 text-center text-sm">Rp {{ number_format($results->get($property->id)->total_mice_revenue ?? 0, 0, ',', '.') }}</td> @endforeach </tr>
                                <tr><td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-800 dark:text-gray-200">Lain-lain</td>@foreach($properties as $property) <td class="px-6 py-4 text-center text-sm">Rp {{ number_format($results->get($property->id)->total_others_revenue ?? 0, 0, ',', '.') }}</td> @endforeach </tr>
                                <tr class="bg-indigo-100 dark:bg-indigo-900/30 font-extrabold text-indigo-800 dark:text-indigo-200"><td class="px-6 py-4 whitespace-nowrap text-sm">GRAND TOTAL PENDAPATAN</td>@foreach($properties as $property) <td class="px-6 py-4 text-center text-sm">Rp {{ number_format($results->get($property->id)->total_overall_revenue ?? 0, 0, ',', '.') }}</td> @endforeach </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-6">
                        <a href="{{ route('admin.properties.compare_page') }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">
                            &larr; Kembali ke Halaman Pilihan
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            // Skrip Javascript Anda dari sebelumnya tidak perlu diubah, saya sertakan kembali untuk kelengkapan
            document.addEventListener('DOMContentLoaded', function () {
                try {
                    const chartData = @json($chartData);
                    
                    if (chartData && Array.isArray(chartData) && chartData.length > 0) {
                        const labels = chartData.map(item => item.label);
                        const revenues = chartData.map(item => item.revenue);
                        const colors = chartData.map(item => item.color);

                        const barCanvas = document.getElementById('comparisonBarChart');
                        if (barCanvas) {
                            new Chart(barCanvas.getContext('2d'), {
                                type: 'bar',
                                data: { labels: labels, datasets: [{ label: 'Total Pendapatan', data: revenues, backgroundColor: colors.map(c => c + '99'), borderColor: colors, borderWidth: 1 }] },
                                options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, ticks: { callback: value => 'Rp ' + new Intl.NumberFormat('id-ID').format(value) } } }, plugins: { legend: { display: false } } }
                            });
                        }

                        const pieCanvas = document.getElementById('comparisonPieChart');
                        if (pieCanvas) {
                            new Chart(pieCanvas.getContext('2d'), {
                                type: 'doughnut',
                                data: { labels: labels, datasets: [{ label: 'Kontribusi Pendapatan', data: revenues, backgroundColor: colors, hoverOffset: 4 }] },
                                options: { responsive: true, maintainAspectRatio: false }
                            });
                        }
                    }
                } catch (e) {
                    console.error('Gagal membuat grafik:', e);
                }
            });
        </script>
    @endpush
</x-admin-layout>