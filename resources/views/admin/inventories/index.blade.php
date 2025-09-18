<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{-- Menampilkan nama properti di header --}}
            Inventaris untuk Properti: {{ $property->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-4">
                        {{-- Tombol Kembali ke halaman pemilihan properti --}}
                        <a href="{{ route('admin.inventories.select') }}" class="text-sm text-gray-600 hover:text-gray-900">
                            &larr; Kembali ke Pilihan Properti
                        </a>
                        {{-- Tombol Tambah Item Baru dengan perbaikan --}}
                        <a href="{{ route('admin.inventories.create', $property) }}" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">Tambah Item Baru</a>
                    </div>

                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stok</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Satuan</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga</th>
                                <th class="px-6 py-3 bg-gray-50"></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($inventories as $inventory)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $inventory->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $inventory->category }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $inventory->quantity }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $inventory->unit }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ number_format($inventory->price, 2, ',', '.') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="{{ route('admin.inventories.edit', $inventory) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                        Tidak ada data inventaris untuk properti ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="mt-4">
                        {{ $inventories->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>