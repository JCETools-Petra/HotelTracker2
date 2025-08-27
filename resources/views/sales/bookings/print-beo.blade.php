<x-print-layout>
    <x-slot name="title">
        BEO - {{ $beo->beo_number }}
    </x-slot>

    {{-- [DISEMPURNAKAN] CSS Khusus untuk mengatur layout cetak A4 dan header/footer --}}
    <style>
        @page {
            size: A4;
            margin: 0.7in; /* Atur margin kertas */

            /* Perintah untuk mencoba menghilangkan header & footer bawaan browser */
            @top-left { content: ""; }
            @top-center { content: ""; }
            @top-right { content: ""; }
            @bottom-left { content: ""; }
            @bottom-center { content: ""; }
            @bottom-right { content: ""; }
        }
        body {
            font-family: 'Helvetica', Arial, sans-serif;
            font-size: 9pt;
            line-height: 1.3;
            color: #1f2937;
        }
        .section-title {
            font-size: 11pt;
            font-weight: bold;
            margin-top: 12px;
            margin-bottom: 4px;
            border-bottom: 1px solid #d1d5db;
            padding-bottom: 2px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-table td {
            padding: 3px 0;
        }
        .detail-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }
        .detail-table th, .detail-table td {
            border: 1px solid #d1d5db;
            padding: 4px 6px;
            text-align: left;
        }
        .detail-table th {
            background-color: #f9fafb;
        }
        .avoid-break {
            page-break-inside: avoid;
        }
    </style>

    {{-- Konten BEO yang akan dicetak (tidak ada perubahan di sini) --}}
    <div>
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold uppercase">Banquet Event Order</h1>
            <p class="text-base text-gray-600">BEO No: {{ $beo->beo_number }}</p>
        </div>
        <table class="info-table mb-4 border-b border-gray-300 pb-4">
            <tr>
                <td class="w-1/2"><strong>Account Name:</strong> {{ $booking->client_name }}</td>
                <td class="w-1/2 text-right"><strong>Date Event:</strong> {{ \Carbon\Carbon::parse($booking->event_date)->format('d F Y') }}</td>
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
        <div>
            <h3 class="section-title">Event Details</h3>
            <table class="detail-table">
                <thead>
                    <tr>
                        <th class="w-[15%]">Time</th><th class="w-[30%]">Event</th><th class="w-[25%]">Room (Setup)</th><th class="w-[10%] text-center">Attend</th><th class="w-[20%]">Remark</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($beo->event_segments ?? [] as $segment)
                        <tr><td>{{ $segment['time'] }}</td><td>{{ $segment['event'] }}</td><td>{{ $segment['room'] }} ({{ $beo->room_setup }})</td><td class="text-center">{{ $segment['attend'] }}</td><td>{{ $segment['remark'] }}</td></tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-gray-500 py-4">Tidak ada detail acara.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="flex gap-6 mt-4 avoid-break">
            <div class="w-1/2"><h3 class="section-title">Banquet Setup</h3><ul class="list-disc list-inside space-y-1 mt-2">@forelse($beo->equipment_details ?? [] as $eq)<li>{{ $eq['item'] }} ({{ $eq['qty'] }}) - {{ $eq['remark'] }}</li>@empty<li>-</li>@endforelse</ul></div>
            <div class="w-1/2"><h3 class="section-title">Menu Details</h3>@forelse($beo->menu_details ?? [] as $menu)<div class="mt-2"><p class="font-bold">{{ $menu['type'] }}</p><p class="pl-2 whitespace-pre-line">{{ $menu['description'] }}</p></div>@empty<p class="mt-2 text-gray-500">-</p>@endforelse</div>
        </div>
        <div class="mt-4 avoid-break"><h3 class="section-title">Information for All Departments</h3><div class="grid grid-cols-2 gap-x-6 gap-y-2 mt-2">@php $departmentNotes = array_filter($beo->department_notes ?? []); @endphp @forelse($departmentNotes as $dept => $note)<div class="flex"><p class="font-bold w-1/3">{{ $dept }}</p><p class="w-2/3">: {{ $note }}</p></div>@empty<p class="text-gray-500 col-span-2">-</p>@endforelse</div></div>
        <div class="flex gap-6 mt-4 avoid-break">
            <div class="w-1/2"><h3 class="section-title">Billing Instruction</h3><table class="w-full mt-2"><tbody><tr><td class="py-1">Paket yang dipilih</td><td class="py-1 text-right">{{ $beo->pricePackage->name ?? 'N/A' }}</td></tr><tr><td class="py-1">Harga per Pax</td><td class="py-1 text-right">Rp {{ number_format($beo->pricePackage->price ?? 0, 0, ',', '.') }}</td></tr><tr><td class="py-1">Jumlah Peserta</td><td class="py-1 text-right">{{ $booking->participants }} Pax</td></tr><tr class="font-bold border-t border-gray-300"><td class="py-1">Total Harga</td><td class="py-1 text-right">Rp {{ number_format($booking->total_price, 0, ',', '.') }}</td></tr></tbody></table></div>
            <div class="w-1/2">@if($beo->notes)<h3 class="section-title">General Notes</h3><p class="mt-2 whitespace-pre-line">{{ $beo->notes }}</p>@endif</div>
        </div>
    </div>

    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</x-print-layout>