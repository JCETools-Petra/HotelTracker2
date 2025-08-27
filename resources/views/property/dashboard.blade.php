<x-property-user-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Dashboard') }}
            </h2>
            <div>
                <a href="{{ route('property.income.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 -ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    Catat Pendapatan
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            {{-- Pesan Sukses --}}
            @if (session('success'))
                <div class="p-4 mb-4 text-sm text-green-700 bg-green-100 rounded-lg dark:bg-green-200 dark:text-green-800" role="alert">
                    <span class="font-medium">Sukses!</span> {{ session('success') }}
                </div>
            @endif

            {{-- Kartu Selamat Datang & Update Okupansi --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Selamat datang, {{ Auth::user()->name }}!</h3>
                    <p class="text-gray-600 dark:text-gray-400 mt-1">Anda mengelola properti <strong>{{ $property->name }}</strong>.</p>
                    
                    <hr class="my-4 dark:border-gray-700">

                    <form id="occupancy-form" action="{{ route('property.occupancy.update') }}" method="POST">
                        @csrf
                        <input type="hidden" name="date" value="{{ today()->toDateString() }}">
                        <div class="flex flex-wrap items-end gap-4">
                            <div>
                                <label for="occupied_rooms" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Update Okupansi Hari Ini ({{ today()->isoFormat('D MMM YYYY') }})</label>
                                <input type="number" name="occupied_rooms" value="{{ $occupancyToday->occupied_rooms ?? 0 }}" class="mt-1 block rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            <button type="submit" id="update-button" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                Update
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            {{-- Kartu Ringkasan Pendapatan --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm">
                    <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300">Total Pendapatan (Bulan Ini)</h3>
                    <p class="text-3xl font-bold text-gray-900 dark:text-gray-100 mt-2">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm">
                    <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300">Pendapatan Kamar (Bulan Ini)</h3>
                    <p class="text-3xl font-bold text-gray-900 dark:text-gray-100 mt-2">Rp {{ number_format($totalRoomRevenue, 0, ',', '.') }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm">
                    <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300">Pendapatan F&B (Bulan Ini)</h3>
                    <p class="text-3xl font-bold text-gray-900 dark:text-gray-100 mt-2">Rp {{ number_format($totalFbRevenue, 0, ',', '.') }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm">
                    <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300">Lain-lain (Bulan Ini)</h3>
                    <p class="text-3xl font-bold text-gray-900 dark:text-gray-100 mt-2">Rp {{ number_format($totalOthersIncome, 0, ',', '.') }}</p>
                </div>
            </div>

            {{-- Tabel Pendapatan Terbaru --}}
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm">
                <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-4">5 Catatan Pendapatan Terbaru</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                            <tr>
                                <th scope="col" class="px-6 py-3">Tanggal</th>
                                <th scope="col" class="px-6 py-3">Total Pendapatan</th>
                                <th scope="col" class="px-6 py-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($latestIncomes as $income)
                                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                    <td class="px-6 py-4 font-medium">{{ $income->date->isoFormat('dddd, D MMMM YYYY') }}</td>
                                    <td class="px-6 py-4 font-semibold">Rp {{ number_format($income->total_revenue, 0, ',', '.') }}</td>
                                    <td class="px-6 py-4">
                                        <a href="{{ route('property.income.edit', $income) }}" class="font-medium text-indigo-600 dark:text-indigo-500 hover:underline">Edit</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-6 py-4 text-center">Belum ada data pendapatan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('occupancy-form');
            const button = document.getElementById('update-button');

            if (form && button) {
                form.addEventListener('submit', function() {
                    // Saat form di-submit...
                    button.disabled = true; // Nonaktifkan tombol
                    button.innerText = 'Memproses...'; // Ubah teks tombol
                });
            }
        });
    </script>
    @endpush
</x-property-user-layout>