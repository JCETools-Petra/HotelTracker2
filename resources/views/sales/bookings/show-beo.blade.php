<x-app-layout>
    <x-slot name="header">
        {{-- Container ini akan disembunyikan saat print --}}
        <div class="header-container">
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    {{ __('Lihat Banquet Event Order (BEO)') }}
                </h2>
                <div class="flex items-center space-x-2">
                    <a href="{{ route('sales.bookings.beo', $booking) }}" class="text-sm px-4 py-2 bg-gray-200 dark:bg-gray-700 rounded-md hover:bg-gray-300">
                        Kembali ke Edit
                    </a>
                    {{-- [DIUBAH] Tombol Cetak kembali menggunakan JavaScript --}}
                    <a href="{{ route('sales.bookings.printBeo', $booking) }}" target="_blank" class="text-sm px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        Cetak
                    </a>
                    <a href="{{ route('sales.documents.beo', $booking) }}" class="text-sm px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                        Download PDF
                    </a>
                </div>
            </div>
        </div>
    </x-slot>

    {{-- Kita beri ID pada container utama BEO agar mudah ditarget --}}
    <div class="py-12">
        <div class="max-w-4xl mx-auto">
            <div class="bg-white dark:bg-gray-800 p-8 rounded-lg shadow-lg" id="beo-content-area">
                {{-- HEADER BEO --}}
                <div class="text-center mb-8">
                    <h1 class="text-2xl font-bold uppercase text-gray-900 dark:text-gray-100">Banquet Event Order</h1>
                    <p class="text-sm text-gray-500">BEO No: {{ $beo->beo_number }}</p>
                </div>

                {{-- INFORMASI UMUM --}}
                <div class="grid grid-cols-2 gap-x-8 gap-y-2 border-b dark:border-gray-700 pb-6 mb-6 text-sm text-gray-700 dark:text-gray-300">
                    <div><strong>Account Name:</strong> {{ $booking->client_name }}</div>
                    <div class="text-right"><strong>Date Event:</strong> {{ \Carbon\Carbon::parse($booking->event_date)->format('d F Y') }}</div>
                    <div><strong>Person Contact:</strong> {{ $booking->person_in_charge }}</div>
                    <div class="text-right"><strong>Time:</strong> {{ \Carbon\Carbon::parse($booking->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($booking->end_time)->format('H:i') }}</div>
                    <div><strong>Telp:</strong> {{ $beo->contact_phone }}</div>
                    <div class="text-right"><strong>Dealed By:</strong> {{ $beo->dealed_by }}</div>
                </div>

                {{-- DETAIL ACARA --}}
                <div class="mb-6">
                    <h3 class="text-lg font-semibold mb-3 border-b pb-1 dark:text-gray-100 dark:border-gray-700">Event Details</h3>
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="p-2 text-left">Time</th>
                                <th class="p-2 text-left">Event</th>
                                <th class="p-2 text-left">Room</th>
                                <th class="p-2 text-center">Attend</th>
                                <th class="p-2 text-left">Remark</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y dark:divide-gray-700">
                            @foreach($beo->event_segments ?? [] as $segment)
                                <tr>
                                    <td class="p-2">{{ $segment['time'] }}</td>
                                    <td class="p-2">{{ $segment['event'] }}</td>
                                    <td class="p-2">{{ $segment['room'] }} ({{ $beo->room_setup }})</td>
                                    <td class="p-2 text-center">{{ $segment['attend'] }}</td>
                                    <td class="p-2">{{ $segment['remark'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- PERLENGKAPAN & MENU --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-6">
                    <div>
                        <h3 class="text-lg font-semibold mb-3 border-b pb-1 dark:text-gray-100 dark:border-gray-700">Banquet Setup</h3>
                        <ul class="list-disc list-inside space-y-1 text-sm">
                            @foreach($beo->equipment_details ?? [] as $eq)
                                <li>{{ $eq['item'] }} ({{ $eq['qty'] }}) - {{ $eq['remark'] }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold mb-3 border-b pb-1 dark:text-gray-100 dark:border-gray-700">Menu Details</h3>
                        @foreach($beo->menu_details ?? [] as $menu)
                            <div class="mb-2">
                                <p class="font-bold text-sm">{{ $menu['type'] }}</p>
                                <p class="text-sm pl-4 whitespace-pre-line">{{ $menu['description'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- CATATAN DEPARTEMEN --}}
                 <div class="mb-6">
                    <h3 class="text-lg font-semibold mb-3 border-b pb-1 dark:text-gray-100 dark:border-gray-700">Information for All Departments</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-2 text-sm">
                        @foreach($beo->department_notes ?? [] as $dept => $note)
                            @if(!empty($note))
                            <div>
                                <p class="font-bold">{{ $dept }}</p>
                                <p class="pl-4">{{ $note }}</p>
                            </div>
                            @endif
                        @endforeach
                    </div>
                </div>
                
                 {{-- BILLING --}}
                 <div class="mb-6">
                    <h3 class="text-lg font-semibold mb-3 border-b pb-1 dark:text-gray-100 dark:border-gray-700">Billing Instruction</h3>
                    <table class="min-w-full text-sm">
                        <tbody class="divide-y dark:divide-gray-700">
                             <tr>
                                <td class="p-2 w-3/4">Paket yang dipilih</td>
                                <td class="p-2 text-right">{{ $beo->pricePackage->name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="p-2">Harga per Pax</td>
                                <td class="p-2 text-right">Rp {{ number_format($beo->pricePackage->price ?? 0, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td class="p-2">Jumlah Peserta</td>
                                <td class="p-2 text-right">{{ $booking->participants }} Pax</td>
                            </tr>
                            <tr class="font-bold bg-gray-50 dark:bg-gray-700">
                                <td class="p-2">Total Harga</td>
                                <td class="p-2 text-right">Rp {{ number_format($booking->total_price, 0, ',', '.') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- GENERAL NOTES --}}
                @if($beo->notes)
                <div class="mt-8">
                     <h3 class="text-lg font-semibold mb-2 border-b pb-1 dark:text-gray-100 dark:border-gray-700">General Notes</h3>
                     <p class="text-sm whitespace-pre-line">{{ $beo->notes }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>
    
    {{-- [DISEMPURNAKAN] CSS Khusus untuk Print --}}
    @push('styles')
    <style>
        @media print {
            /* Sembunyikan semua elemen di luar area konten utama */
            body > div > aside,           /* Sidebar */
            body > div > div > header,    /* Top header bar */
            .header-container           /* Header halaman ini */
            {
                display: none !important;
            }

            /* Atur ulang layout utama agar konten mengisi halaman */
            body > div[x-data] {
                display: block !important;
            }
            .flex-1.flex.flex-col {
                margin: 0 !important;
            }
            main.flex-1 {
                padding: 0 !important;
                margin: 0 !important;
            }
            main .container {
                 padding: 0 !important;
                 margin: 0 !important;
                 max-width: 100% !important;
            }
            .py-12 {
                padding: 0 !important;
            }

            /* Atur agar BEO itu sendiri yang menjadi konten utama */
            #beo-content-area {
                box-shadow: none !important;
                border-radius: 0 !important;
                padding: 0 !important;
            }

            /* Penyesuaian warna untuk print (opsional, tapi disarankan) */
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                background-color: #ffffff !important;
            }
            .dark .dark\:bg-gray-800, .dark .bg-gray-50.dark\:bg-gray-700 {
                background-color: #ffffff !important;
            }
            .dark .dark\:text-gray-100, .dark .dark\:text-gray-200, .dark .dark\:text-gray-300, .dark .dark\:text-gray-400 {
                color: #000000 !important;
            }
            .border-b {
                border-color: #cccccc !important;
            }
        }
    </style>
    @endpush
</x-app-layout>