<x-inventory-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Dashboard Inventaris: {{ $property->name }}
        </h2>
    </x-slot>

    <div class="max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-4">
            {{-- Form Pencarian --}}
            <form method="GET" action="{{ route('inventory.dashboard') }}" class="w-1/2">
                <div class="flex">
                    <x-text-input type="text" name="search" :value="$search ?? ''" placeholder="Cari item..." class="w-full rounded-r-none" />
                    <x-primary-button class="rounded-l-none">Cari</x-primary-button>
                </div>
            </form>
            <a href="{{ route('inventory.items.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md font-semibold text-xs uppercase hover:bg-blue-700">
                + Tambah Item
            </a>
        </div>
        
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <x-notification />
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Item</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kategori</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kondisi</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Stok</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse ($inventories as $item)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $item->name }}</div>
                                        <div class="text-xs text-gray-500">{{ $item->item_code }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $item->category->name ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ Str::title($item->condition) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-gray-900 dark:text-gray-100">{{ $item->stock }} {{ $item->unit }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-4">
                                        <a href="{{ route('inventory.items.edit', $item) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                        <form action="{{ route('inventory.items.destroy', $item) }}" method="POST" class="inline" onsubmit="return confirm('Anda yakin ingin menghapus item ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">Tidak ada data inventaris.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    {{ $inventories->links() }}
                </div>
            </div>
        </div>
    </div>
</x-inventory-layout>