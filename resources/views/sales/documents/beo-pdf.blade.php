<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>BEO {{ $beo->beo_number }}</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 10px; line-height: 1.4; color: #333; }
        .page-break { page-break-after: always; }
        .container { width: 100%; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { font-size: 18px; margin: 0; text-transform: uppercase; }
        .header p { font-size: 12px; margin: 0; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th, td { border: 1px solid #ccc; padding: 5px; text-align: left; vertical-align: top;}
        th { background-color: #f2f2f2; font-weight: bold; }
        .section-title { font-size: 14px; font-weight: bold; margin-top: 20px; margin-bottom: 5px; border-bottom: 1px solid #333; padding-bottom: 2px;}
        .text-right { text-align: right; }
        .no-border td { border: none; }
        ul { list-style: disc; padding-left: 20px; margin: 0; }
        li { margin-bottom: 2px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Banquet Event Order</h1>
            <p>BEO No: {{ $beo->beo_number }}</p>
        </div>

        {{-- INFORMASI UMUM --}}
        <table class="no-border">
            <tr>
                <td width="50%"><strong>Account Name:</strong> {{ $booking->client_name }}</td>
                <td width="50%" class="text-right"><strong>Date Event:</strong> {{ \Carbon\Carbon::parse($booking->event_date)->format('d F Y') }}</td>
            </tr>
            <tr>
                <td><strong>Person Contact:</strong> {{ $booking->person_in_charge }}</td>
                <td class="text-right"><strong>Time:</strong> {{ \Carbon\Carbon::parse($booking->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($booking->end_time)->format('H:i') }}</td>
            </tr>
            <tr>
                <td><strong>Telp:</strong> {{ $beo->contact_phone }}</td>
                <td class="text-right"><strong>Dealed By:</strong> {{ $beo->dealed_by }}</td>
            </tr>
        </table>
        
        {{-- DETAIL ACARA --}}
        <h3 class="section-title">Event Details</h3>
        <table>
            <thead>
                <tr>
                    <th width="15%">Time</th>
                    <th width="30%">Event</th>
                    <th width="25%">Room (Setup)</th>
                    <th width="10%">Attend</th>
                    <th width="20%">Remark</th>
                </tr>
            </thead>
            <tbody>
                @foreach($beo->event_segments ?? [] as $segment)
                    <tr>
                        <td>{{ $segment['time'] }}</td>
                        <td>{{ $segment['event'] }}</td>
                        <td>{{ $segment['room'] }} ({{ $beo->room_setup }})</td>
                        <td style="text-align: center;">{{ $segment['attend'] }}</td>
                        <td>{{ $segment['remark'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- PERLENGKAPAN & MENU --}}
        <table>
            <tr>
                <td width="50%" style="vertical-align: top;">
                    <h3 class="section-title" style="margin-top: 0;">Banquet Setup</h3>
                     @if(!empty($beo->equipment_details))
                        <ul>
                        @foreach($beo->equipment_details as $eq)
                            <li>{{ $eq['item'] }} ({{ $eq['qty'] }}) - {{ $eq['remark'] }}</li>
                        @endforeach
                        </ul>
                    @endif
                </td>
                <td width="50%" style="vertical-align: top;">
                    <h3 class="section-title" style="margin-top: 0;">Menu Details</h3>
                    @foreach($beo->menu_details ?? [] as $menu)
                        <p><strong>{{ $menu['type'] }}</strong><br>{{ $menu['description'] }}</p>
                    @endforeach
                </td>
            </tr>
        </table>
        
        {{-- CATATAN DEPARTEMEN --}}
        <h3 class="section-title">Information for All Departments</h3>
        <table>
            @foreach($beo->department_notes ?? [] as $dept => $note)
                @if(!empty($note))
                <tr>
                    <td width="25%"><strong>{{ $dept }}</strong></td>
                    <td>{{ $note }}</td>
                </tr>
                @endif
            @endforeach
        </table>
        
        {{-- BILLING --}}
        <h3 class="section-title">Billing Instruction</h3>
        <table>
            <tr>
                <td width="75%">Paket {{ $beo->pricePackage->name ?? 'N/A' }} ({{ $booking->participants }} Pax @ Rp {{ number_format($beo->pricePackage->price ?? 0, 0, ',', '.') }})</td>
                <td class="text-right">Rp {{ number_format($booking->total_price, 0, ',', '.') }}</td>
            </tr>
            <tr style="font-weight: bold; background-color: #f2f2f2;">
                <td>Total</td>
                <td class="text-right">Rp {{ number_format($booking->total_price, 0, ',', '.') }}</td>
            </tr>
        </table>

    </div>
</body>
</html>