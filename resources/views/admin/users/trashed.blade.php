<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Pengguna Dinonaktifkan') }}
            </h2>
            <nav class="flex space-x-4">
                <x-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.index')">
                    {{ __('Kembali ke Daftar Pengguna Aktif') }}
                </x-nav-link>
                {{-- Link navigasi admin lainnya bisa ditambahkan di sini --}}
            </nav>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    
                    @if (session('success'))
                        <div class="mb-4 p-3 bg-green-100 dark:bg-green-700 text-green-700 dark:text-green-200 border border-green-300 dark:border-green-600 rounded-md">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="mb-4 p-3 bg-red-100 dark:bg-red-700 text-red-700 dark:text-red-200 border border-red-300 dark:border-red-600 rounded-md">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if ($trashedUsers->isEmpty())
                        <p class="text-center text-gray-500 dark:text-gray-400 py-4">
                            Tidak ada pengguna yang dinonaktifkan saat ini.
                        </p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Nama</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Email</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Peran</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Properti Dikelola</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Dinonaktifkan Pada</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach ($trashedUsers as $user)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">{{ $user->name }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ $user->email }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                    @if($user->role === 'admin') bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100 @endif
                                                    @if($user->role === 'pengguna_properti') bg-gray-200 text-gray-700 dark:bg-gray-600 dark:text-gray-200 @endif">
                                                    {{ Str::title(str_replace('_', ' ', $user->role)) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                                {{ $user->property->name ?? '-' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                                {{ $user->deleted_at ? $user->deleted_at->isoFormat('D MMM YYYY, HH:mm') : '-' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                                <form method="POST" action="{{ route('admin.users.restore', $user->id) }}" class="inline-block">
                                                    @csrf
                                                    @method('PUT')
                                                    <button type="submit" class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-200" onclick="return confirm('Apakah Anda yakin ingin memulihkan pengguna {{ addslashes($user->name) }}?');">Pulihkan</button>
                                                </form>
                                                {{-- Tombol Hapus Permanen akan kita implementasikan nanti jika perlu --}}
                                                {{-- 
                                                <form method="POST" action="{{ route('admin.users.forceDelete', $user->id) }}" class="inline-block" onsubmit="return confirm('PERINGATAN! Menghapus pengguna ini secara permanen akan menghilangkan semua data terkait. Apakah Anda benar-benar yakin?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-700 hover:text-red-900 dark:text-red-500 dark:hover:text-red-300 ml-2">Hapus Permanen</button>
                                                </form> 
                                                --}}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4">
                            {{ $trashedUsers->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>