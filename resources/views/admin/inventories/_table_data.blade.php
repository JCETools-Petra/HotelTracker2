{{-- File ini hanya berisi konten yang akan di-refresh oleh AJAX --}}

<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
        <thead class="bg-gray-50 dark:bg-gray-700">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Item</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stok</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tgl Pembelian</th>
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
                        <div class="text-xs text-indigo-400">{{ $inventory->category->name ?? 'Tanpa Kategori' }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $inventory->stock }} {{ $inventory->unit }}</td>
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
                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                        @if(isset($search) && $search)
                            Tidak ada inventaris yang cocok dengan pencarian "{{ $search }}".
                        @else
                            Belum ada data inventaris untuk properti ini.
                        @endif
                    </td>
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

{{-- Paginasi --}}
<div class="mt-4">
    {{ $inventories->links() }}
</div>