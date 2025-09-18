<table>
    {{-- Hapus semua atribut colspan dari sini --}}
    <tr>
        <td style="font-size: 16px; font-weight: bold;">Laporan Analisis Kinerja (KPI) - {{ $monthName }}</td>
    </tr>
    <tr>
        <td style="font-style: italic;">Properti: {{ $selectedProperty ? $selectedProperty->name : 'Semua Properti' }}</td>
    </tr>
    <tr></tr>

    @if($kpiData)
    <tr>
        <td style="font-weight: bold;">METRIK UTAMA BULAN INI</td><td></td>
        <td style="font-weight: bold;">RINCIAN PENDAPATAN</td><td></td>
        <td style="font-weight: bold;">RINCIAN KAMAR TERJUAL</td><td></td>
    </tr>
    <tr>
        <td>Total Pendapatan</td><td>{{ $kpiData['totalRevenue'] }}</td>
        <td>Offline</td><td>{{ $kpiData['revenueBreakdown']['Offline'] }}</td>
        <td>Offline</td><td>{{ $kpiData['roomsSoldBreakdown']['Offline'] }}</td>
    </tr>
    <tr>
        <td>Okupansi Rata-rata</td><td>{{ $kpiData['avgOccupancy'] }}%</td>
        <td>Online</td><td>{{ $kpiData['revenueBreakdown']['Online'] }}</td>
        <td>Online</td><td>{{ $kpiData['roomsSoldBreakdown']['Online'] }}</td>
    </tr>
    <tr>
        <td>Average Room Rate (ARR)</td><td>{{ $kpiData['avgArr'] }}</td>
        <td>Travel Agent</td><td>{{ $kpiData['revenueBreakdown']['Travel Agent'] }}</td>
        <td>Travel Agent</td><td>{{ $kpiData['roomsSoldBreakdown']['Travel Agent'] }}</td>
    </tr>
    <tr>
        <td>Revenue Per Available Room (RevPAR)</td><td>{{ $kpiData['revPar'] }}</td>
        <td>Government</td><td>{{ $kpiData['revenueBreakdown']['Government'] }}</td>
        <td>Government</td><td>{{ $kpiData['roomsSoldBreakdown']['Government'] }}</td>
    </tr>
    <tr>
        <td>Total Kamar Terjual</td><td>{{ $kpiData['totalRoomsSold'] }}</td>
        <td>Corporate</td><td>{{ $kpiData['revenueBreakdown']['Corporate'] }}</td>
        <td>Corporate</td><td>{{ $kpiData['roomsSoldBreakdown']['Corporate'] }}</td>
    </tr>
    <tr>
        <td></td><td></td>
        <td>Afiliasi</td><td>{{ $kpiData['revenueBreakdown']['Afiliasi'] }}</td>
        <td>Afiliasi</td><td>{{ $kpiData['roomsSoldBreakdown']['Afiliasi'] }}</td>
    </tr>
     <tr>
        <td></td><td></td>
        <td>MICE/Event</td><td>{{ $kpiData['revenueBreakdown']['MICE/Event'] }}</td>
        <td>House Use</td><td>{{ $kpiData['roomsSoldBreakdown']['House Use'] }}</td>
    </tr>
    <tr>
        <td></td><td></td>
        <td></td><td></td>
        <td>Compliment</td><td>{{ $kpiData['roomsSoldBreakdown']['Compliment'] }}</td>
    </tr>
    @endif
    
    <tr></tr><tr></tr>

    <thead>
        <tr>
            <th>Tanggal</th>
            <th>Pendapatan</th>
            <th>ARR</th>
            <th>Okupansi (%)</th>
            <th>Kamar Terjual</th>
        </tr>
    </thead>
    <tbody>
        @if($dailyData)
            @foreach($dailyData as $data)
                <tr>
                    <td>{{ $data['date'] }}</td>
                    <td>{{ $data['revenue'] }}</td>
                    <td>{{ $data['arr'] }}</td>
                    <td>{{ $data['occupancy'] }}</td>
                    <td>{{ $data['rooms_sold'] }}</td>
                </tr>
            @endforeach
        @endif
    </tbody>
</table>