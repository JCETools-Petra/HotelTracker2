<table>
    {{-- Baris Judul --}}
    <tr>
        <th colspan="8" style="font-size: 16px; font-weight: bold;">Laporan Analisis Kinerja (KPI)</th>
    </tr>
    <tr>
        <th colspan="8" style="font-weight: bold;">Properti: {{ htmlspecialchars($property->name ?? 'Semua Properti') }}</th>
    </tr>
    <tr>
        <th colspan="8" style="font-weight: bold;">Bulan: {{ htmlspecialchars($monthName) }}</th>
    </tr>
    <tr><td colspan="8"></td></tr>

    {{-- Header untuk 3 Kolom Rincian --}}
    <tr>
        <td style="font-weight: bold;">METRIK UTAMA</td><td></td>
        <td style="width: 5px;"></td>
        <td style="font-weight: bold;">RINCIAN PENDAPATAN</td><td></td>
        <td style="width: 5px;"></td>
        <td style="font-weight: bold;">RINCIAN KAMAR TERJUAL</td><td></td>
    </tr>

    {{-- Data untuk 3 Kolom --}}
    @php
        $kpiItems = [
            'Total Pendapatan' => $kpiData['totalRevenue'],
            'Okupansi Rata-rata' => $kpiData['avgOccupancy'] / 100,
            'Average Room Rate (ARR)' => $kpiData['avgArr'],
            'Revenue Per Available Room (RevPAR)' => $kpiData['revPar'],
            'Resto Revenue Per Room (Sold)' => $kpiData['restoRevenuePerRoom'],
        ];
        
        // Gabungkan Rincian Pendapatan Kamar & Lainnya
        $revenueDetails = array_merge($kpiData['revenueBreakdown'], [
            'Total Pendapatan Kamar' => $kpiData['totalRoomRevenue'],
            '' => '', // Spacer
            'Breakfast' => $kpiData['totalBreakfastRevenue'],
            'Lunch' => $kpiData['totalLunchRevenue'],
            'Dinner' => $kpiData['totalDinnerRevenue'],
            'Total F&B' => $kpiData['totalFbRevenue'],
            'Lain-lain' => $kpiData['totalOtherRevenue'],
        ]);

        $roomsSoldDetails = $kpiData['roomsSoldBreakdown'];
        $roomsSoldDetails['Total Kamar Terjual'] = $kpiData['totalRoomsSold'];

        $kpiKeys = array_keys($kpiItems);
        $revenueKeys = array_keys($revenueDetails);
        $roomsKeys = array_keys($roomsSoldDetails);
        $maxRows = max(count($kpiKeys), count($revenueKeys), count($roomsKeys));
    @endphp

    @for ($i = 0; $i < $maxRows; $i++)
    <tr>
        {{-- Kolom 1: Metrik Utama --}}
        <td>{{ htmlspecialchars($kpiKeys[$i] ?? '') }}</td>
        <td>{{ isset($kpiKeys[$i]) ? htmlspecialchars($kpiItems[$kpiKeys[$i]]) : '' }}</td>
        <td></td>

        {{-- Kolom 2: Rincian Pendapatan --}}
        <td>{{ htmlspecialchars($revenueKeys[$i] ?? '') }}</td>
        <td>{{ isset($revenueKeys[$i]) ? htmlspecialchars($revenueDetails[$revenueKeys[$i]]) : '' }}</td>
        <td></td>

        {{-- Kolom 3: Rincian Kamar Terjual --}}
        <td>{{ htmlspecialchars($roomsKeys[$i] ?? '') }}</td>
        <td>{{ isset($roomsKeys[$i]) ? htmlspecialchars($roomsSoldDetails[$roomsKeys[$i]]) : '' }}</td>
    </tr>
    @endfor
    
    <tr><td colspan="8"></td></tr>
    
    {{-- Tabel Rincian Harian --}}
    <thead>
        <tr><th colspan="5" style="font-weight: bold;">Tabel Rincian Harian</th></tr>
        <tr>
            <th>Tanggal</th>
            <th>Pendapatan</th>
            <th>Okupansi (%)</th>
            <th>ARR</th>
            <th>Kamar Terjual</th>
        </tr>
    </thead>
    <tbody>
        @foreach($dailyData as $data)
            <tr>
                <td>{{ htmlspecialchars($data['date']) }}</td>
                <td>{{ htmlspecialchars($data['revenue']) }}</td>
                <td>{{ htmlspecialchars($data['occupancy'] / 100) }}</td>
                <td>{{ htmlspecialchars($data['arr']) }}</td>
                <td>{{ htmlspecialchars($data['rooms_sold']) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>