<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Log Aktivitas Pengguna') }}
        </h2>
    </x-slot>

    {{-- ======================= AWAL BLOK YANG DIUBAH ======================= --}}
    {{-- Deklarasi x-data dipindahkan ke sini untuk mencakup modal dan tabel --}}
    <div x-data="{ open: false, log: {} }" class="py-12">
    {{-- ======================= AKHIR BLOK YANG DIUBAH ====================== --}}

        {{-- Modal untuk menampilkan detail perubahan --}}
        <div x-show="open" @keydown.window.escape="open = false" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50" style="display: none;" x-cloak>
            <div @click.away="open = false" class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-2xl mx-4">
                <div class="p-6">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">Detail Log</h3>
                    
                    <div class="mt-4 space-y-4">
                        <div>
                            <h4 class="font-semibold text-gray-700 dark:text-gray-300">Informasi Pengguna</h4>
                            <div class="text-sm text-gray-600 dark:text-gray-400">
                                <p><strong>IP Address:</strong> <span x-text="log.ip_address || 'N/A'"></span></p>
                                <p><strong>User Agent:</strong> <span x-text="log.user_agent || 'N/A'" class="break-all"></span></p>
                            </div>
                        </div>

                        <div x-show="log.changes && Object.keys(log.changes).length > 0">
                            <h4 class="font-semibold text-gray-700 dark:text-gray-300">Rincian Perubahan</h4>
                            <div class="overflow-x-auto">
                                <table class="min-w-full mt-2">
                                    <thead class="bg-gray-100 dark:bg-gray-700">
                                        <tr>
                                            <th class="px-4 py-2 text-left">Field</th>
                                            <th class="px-4 py-2 text-left">Nilai Lama</th>
                                            <th class="px-4 py-2 text-left">Nilai Baru</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-for="(change, attribute) in log.changes" :key="attribute">
                                            <tr class="border-b dark:border-gray-700">
                                                <td class="px-4 py-2 font-mono text-sm" x-text="attribute"></td>
                                                <td class="px-4 py-2 font-mono text-sm text-red-500" x-text="change.old"></td>
                                                <td class="px-4 py-2 font-mono text-sm text-green-500" x-text="change.new"></td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 text-right">
                        <button @click="open = false" class="px-4 py-2 bg-gray-200 dark:bg-gray-600 text-gray-800 dark:text-gray-200 rounded-md hover:bg-gray-300">Tutup</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Konten utama halaman --}}
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="mb-4 bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm">
                <form action="{{ route('admin.activity_log.index') }}" method="GET">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="md:col-span-2">
                            <label for="search" class="sr-only">Cari</label>
                            <input type="text" name="search" id="search" placeholder="Cari deskripsi atau nama pengguna..." 
                                   class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm text-sm"
                                   value="{{ request('search') }}">
                        </div>
                        <div>
                            <label for="start_date" class="sr-only">Dari Tanggal</label>
                            <input type="date" name="start_date" id="start_date"
                                   class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm text-sm"
                                   value="{{ request('start_date') }}">
                        </div>
                        <div>
                            <label for="end_date" class="sr-only">Sampai Tanggal</label>
                            <input type="date" name="end_date" id="end_date"
                                   class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm text-sm"
                                   value="{{ request('end_date') }}">
                        </div>
                    </div>
                    <div class="mt-4 flex justify-end space-x-2">
                        <a href="{{ route('admin.activity_log.index') }}" class="px-4 py-2 bg-gray-300 dark:bg-gray-600 text-gray-800 dark:text-gray-200 rounded-md hover:bg-gray-400 text-sm">Reset</a>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm">Filter</button>
                    </div>
                </form>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Pengguna</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Deskripsi</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Waktu</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Detail</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse ($logs as $log)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $log->user->name ?? 'Sistem' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $log->description }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $log->created_at->format('d M Y, H:i') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            {{-- Tombol ini sekarang berada dalam cakupan x-data --}}
                                            <button @click="open = true; log = {{ json_encode($log) }}" class="px-2 py-1 text-xs bg-blue-600 text-white rounded hover:bg-blue-700">
                                                Lihat Detail
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                                            @if(request()->hasAny(['search', 'start_date', 'end_date']))
                                                Tidak ada aktivitas yang cocok dengan filter Anda.
                                            @else
                                                Tidak ada aktivitas yang tercatat.
                                            @endif
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $logs->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>