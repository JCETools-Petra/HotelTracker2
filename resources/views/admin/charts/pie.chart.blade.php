@php
    // Variabel $chartId dan $data diharapkan dari parent view.
    // Jika $chartId tidak ada, kita set default untuk menghindari error.
    $chartId = $chartId ?? 'pieChart'; 

    // Definisikan warna yang sama dengan yang ada di JavaScript untuk digunakan di legenda HTML
    $colors = [
        'rgba(255, 99, 132, 0.8)', 'rgba(54, 162, 235, 0.8)', 'rgba(255, 206, 86, 0.8)',
        'rgba(75, 192, 192, 0.8)', 'rgba(153, 102, 255, 0.8)', 'rgba(255, 159, 64, 0.8)',
        'rgba(199, 199, 199, 0.8)', 'rgba(83, 102, 255, 0.8)', 'rgba(4, 209, 130, 0.8)',
        'rgba(240, 99, 132, 0.8)'
    ];
@endphp

<div class="flex flex-col md:flex-row items-center gap-6">
    <div class="w-full md:w-1/2 lg:w-2/3 h-64 md:h-auto">
        <canvas id="{{ $chartId }}"></canvas>
    </div>

    <div class="w-full md:w-1/2 lg:w-1/3 space-y-2">
        @if(isset($data) && !empty($data['labels']))
            @foreach($data['labels'] as $index => $label)
                @if($data['data'][$index] > 0)
                    <div class="flex items-center p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                        <span class="w-4 h-4 rounded-full mr-3 flex-shrink-0" style="background-color: {{ $colors[$index % count($colors)] }};"></span>
                        <div class="flex justify-between w-full text-sm">
                            <span class="text-gray-700 dark:text-gray-300 mr-2">{{ $label }}</span>
                            <span class="font-bold text-gray-900 dark:text-gray-100 text-right">
                                Rp {{ number_format($data['data'][$index], 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                @endif
            @endforeach
        @else
            <p class="text-gray-500">Tidak ada data untuk ditampilkan.</p>
        @endif
    </div>
</div>

{{-- Memastikan Chart.js hanya di-load sekali, jika belum ada --}}
@once
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endonce

<script>
    // Membungkus script dalam fungsi agar tidak terjadi konflik variabel jika ada beberapa chart
    (function() {
        // Cek jika data ada sebelum membuat chart
        const pieChartData_{{ $chartId }} = @json($data ?? ['labels' => [], 'data' => []]);
        if (pieChartData_{{ $chartId }}.labels.length === 0) {
            return; // Jangan buat chart jika tidak ada data
        }

        const ctx_{{ $chartId }} = document.getElementById('{{ $chartId }}')?.getContext('2d');
        if (!ctx_{{ $chartId }}) {
            return; // Jangan lanjutkan jika canvas tidak ditemukan
        }

        new Chart(ctx_{{ $chartId }}, {
            type: 'pie',
            data: {
                labels: pieChartData_{{ $chartId }}.labels,
                datasets: [{
                    label: 'Pendapatan',
                    data: pieChartData_{{ $chartId }}.data,
                    backgroundColor: @json($colors),
                    borderColor: pieChartData_{{ $chartId }}.labels.map(() => '#ffffff'),
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false // Legenda bawaan tetap nonaktif
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed !== null) {
                                    label += new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(context.parsed);
                                }
                                return label;
                            }
                        }
                    }
                }
            }
        });
    })();
</script>