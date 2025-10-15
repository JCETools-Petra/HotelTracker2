<x-admin-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Bagian Header Halaman --}}
            <div class="flex justify-between items-center mb-4">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-800 dark:text-gray-200">Inventaris untuk {{ $property->name }}</h1>
                    <a href="{{ route('admin.inventories.select') }}" class="text-sm text-indigo-600 hover:text-indigo-900">Kembali ke pemilihan properti</a>
                </div>
                <a href="{{ route('admin.inventories.create', ['property_id' => $property->id]) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md font-semibold text-xs uppercase hover:bg-indigo-700">
                    Tambah Item Baru
                </a>
            </div>

            {{-- Notifikasi Sukses --}}
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded" role="alert">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Tabel Daftar Inventaris --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Item</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stok</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tgl Pembelian</th> {{-- <-- BARU --}}
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Harga Satuan</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Harga</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kondisi</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @php $grandTotal = 0; @endphp
                                @forelse ($inventories as $inventory)
                                    @php
                                        $totalPrice = $inventory->stock * $inventory->unit_price;
                                        $grandTotal += $totalPrice;
                                    @endphp
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                            {{ $inventory->name }}
                                            <div class="text-xs text-gray-500">{{ $inventory->item_code }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $inventory->stock }} {{ $inventory->unit }}</td>
                                        {{-- DATA TANGGAL PEMBELIAN (BARU) --}}
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                            {{ $inventory->purchase_date ? \Carbon\Carbon::parse($inventory->purchase_date)->format('d M Y') : 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">Rp {{ number_format($inventory->unit_price, 0, ',', '.') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">Rp {{ number_format($totalPrice, 0, ',', '.') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ ucfirst($inventory->condition) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <a href="{{ route('admin.inventories.edit', $inventory) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                            <form action="{{ route('admin.inventories.destroy', $inventory) }}" method="POST" class="inline-block ml-4" onsubmit="return confirm('Anda yakin ingin menghapus item ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">Belum ada data inventaris untuk properti ini.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                             <tfoot class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <td colspan="4" class="px-6 py-3 text-right text-sm font-bold text-gray-600 dark:text-gray-200 uppercase">Total Nilai Inventaris</td>
                                    <td class="px-6 py-3 text-left text-sm font-bold text-gray-800 dark:text-white">Rp {{ number_format($grandTotal, 0, ',', '.') }}</td>
                                    <td colspan="2"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div class="mt-4">{{ $inventories->appends(['property_id' => $property->id])->links() }}</div>
                </div>
            </div>

            {{-- Penjelasan Kolom Aksi & Legenda Kategori --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mt-8">
                <div class="p-6 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-200 border-b pb-3 dark:border-gray-700">
                        Penjelasan Kolom Aksi
                    </h3>
                    <ul class="mt-4 space-y-3 text-gray-700 dark:text-gray-300">
                        <li><span class="font-bold text-indigo-500">Edit:</span> Untuk mengubah detail item, seperti nama, stok, harga, atau kondisinya.</li>
                        <li><span class="font-bold text-red-500">Hapus:</span> Untuk menghapus item secara permanen dari daftar inventaris.</li>
                    </ul>
                </div>

                <div class="p-6 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-200 border-b pb-3 dark:border-gray-700">
                        Legenda Kode Kategori
                    </h3>
                    <ul class="mt-4 space-y-2">
                        @forelse($allCategories as $category)
                            <li class="flex items-center text-gray-700 dark:text-gray-300">
                                <span class="font-bold w-20">{{ $category->category_code }}:</span>
                                <span>{{ $category->name }}</span>
                            </li>
                        @empty
                            <li class="text-gray-500">Belum ada kategori yang dibuat.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
            
        </div>
    </div>
</x-admin-layout>