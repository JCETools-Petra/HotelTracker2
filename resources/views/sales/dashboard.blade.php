<x-sales-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Sales Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Statistik Cards --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Booking (Bulan Ini)</h3>
                    <p class="mt-2 text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ $totalBookingThisMonth }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Booking Pasti (Bulan Ini)</h3>
                    <p class="mt-2 text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ $confirmedBookingThisMonth }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Estimasi Pendapatan (Bulan Ini)</h3>
                    <p class="mt-2 text-3xl font-semibold text-gray-900 dark:text-gray-100">Rp {{ number_format($estimatedRevenueThisMonth, 0, ',', '.') }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Peserta (Bulan Ini)</h3>
                    <p class="mt-2 text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ number_format($totalParticipantsThisMonth, 0, ',', '.') }}</p>
                </div>
            </div>

            {{-- Jadwal Event & Booking Terbaru --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- PERBAIKAN PADA BAGIAN INI --}}
                <div class="lg:col-span-2 bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
                    <h3 class="font-semibold text-lg text-gray-900 dark:text-gray-100 mb-4">Jadwal Event (7 Hari ke Depan)</h3>
                    <div class="space-y-4">
                        @forelse ($upcomingEvents as $event)
                            <div class="flex items-start space-x-4">
                                <div class="flex-shrink-0 text-center">
                                    <div class="bg-indigo-100 dark:bg-indigo-900 text-indigo-800 dark:text-indigo-200 rounded-md px-3 py-1">
                                        <p class="text-2xl font-bold">{{ $event->event_date->format('d') }}</p>
                                        <p class="text-xs uppercase">{{ $event->event_date->format('M') }}</p>
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <p class="font-semibold text-gray-800 dark:text-gray-200">{{ $event->client_name }}</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        [ {{ $event->person_in_charge }} - {{ $event->event_type }} - {{ $event->miceCategory->name ?? 'N/A' }} ]
                                    </p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                        <span class="font-medium">{{ number_format($event->participants, 0, ',', '.') }}</span> Peserta
                                    </p>
                                </div>
                                <div class="text-sm text-gray-600 dark:text-gray-300 text-right">
                                    <p>{{ \Carbon\Carbon::parse($event->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($event->end_time)->format('H:i') }}</p>
                                    <p class="text-xs text-gray-400">{{ $event->room->name ?? 'N/A' }}</p>
                                </div>
                            </div>
                        @empty
                            <p class="text-gray-500 dark:text-gray-400">Tidak ada event dalam 7 hari ke depan.</p>
                        @endforelse
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
                    <h3 class="font-semibold text-lg text-gray-900 dark:text-gray-100 mb-4">Booking Terbaru</h3>
                    @if ($latestBooking)
                        <div>
                            <p class="font-semibold text-gray-800 dark:text-gray-200">{{ $latestBooking->client_name }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Dibuat pada {{ $latestBooking->created_at->format('d M Y') }}</p>
                            <span class="mt-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                @if($latestBooking->status == 'Booking Pasti') bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100
                                @elseif($latestBooking->status == 'Booking Sementara') bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100
                                @else bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100 @endif">
                                {{ $latestBooking->status }}
                            </span>
                        </div>
                    @else
                        <p class="text-gray-500 dark:text-gray-400">Belum ada booking.</p>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-sales-layout>
