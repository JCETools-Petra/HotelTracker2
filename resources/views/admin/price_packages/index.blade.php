<x-admin-layout>
    <div class="flex justify-between items-center mb-4">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Manajemen Paket Harga</h2>
        @can('manage-data')
            <a href="{{ route('admin.price-packages.create') }}" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">+ Tambah Paket</a>
        @endcan
    </div>
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <table class="min-w-full divide-y divide-gray-200">
            <thead>
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium">Nama Paket</th>
                    <th class="px-6 py-3 text-left text-xs font-medium">Harga</th>
                    <th class="px-6 py-3 text-left text-xs font-medium">Status</th>
                    @can('manage-data')
                        <th class="px-6 py-3 text-left text-xs font-medium">Aksi</th>
                    @endcan
                </tr>
            </thead>
            <tbody>
                @forelse($packages as $package)
                    <tr>
                        <td class="px-6 py-4">{{ $package->name }}</td>
                        <td class="px-6 py-4">Rp {{ number_format($package->price, 0, ',', '.') }}</td>
                        <td class="px-6 py-4">{{ $package->is_active ? 'Aktif' : 'Tidak Aktif' }}</td>
                        @can('manage-data')
                            <td class="px-6 py-4">
                                <a href="{{ route('admin.price-packages.edit', $package) }}" class="text-indigo-600">Edit</a>
                                <form action="{{ route('admin.price-packages.destroy', $package) }}" method="POST" class="inline-block ml-2" onsubmit="return confirm('Hapus paket ini?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600">Hapus</button>
                                </form>
                            </td>
                        @endcan
                    </tr>
                @empty
                    <tr>
                        <td colspan="@can('manage-data') 4 @else 3 @endcan" class="px-6 py-4 text-center">
                            Tidak ada paket harga ditemukan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-admin-layout>