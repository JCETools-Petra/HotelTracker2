<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Manajemen Pengguna') }}
            </h2>
            <nav class="flex items-center space-x-4 mt-2 sm:mt-0">
                <x-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.index')">
                    {{ __('Pengguna Aktif') }}
                </x-nav-link>
                <x-nav-link :href="route('admin.users.trashed')" :active="request()->routeIs('admin.users.trashed')">
                    {{ __('Pengguna Dinonaktifkan') }}
                </x-nav-link>
                
                @can('manage-data')
                    <a href="{{ route('admin.users.create') }}"
                       class="inline-flex items-center px-4 py-2 bg-blue-600 dark:bg-blue-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 dark:hover:bg-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                        {{ __('+ Tambah Pengguna') }}
                    </a>
                @endcan
            </nav>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    
                    <x-notification />

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Nama</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Email</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Peran</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Properti Dikelola</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Terdaftar Sejak</th>
                                    @can('manage-data')
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Aksi</th>
                                    @endcan
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse ($users as $user)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">{{ $user->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ $user->email }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                            
                                            {{-- BLOK LOGIKA WARNA YANG DIPERBAIKI --}}
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                                @if($user->role === 'admin') bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100
                                                @elseif($user->role === 'owner') bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100
                                                @elseif($user->role === 'pengurus') bg-gray-200 text-gray-800 dark:bg-gray-600 dark:text-gray-100
                                                @elseif($user->role === 'pengguna_properti') bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100
                                                @elseif($user->role === 'sales') bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100
                                                @elseif($user->role === 'inventaris') bg-purple-100 text-purple-800 dark:bg-purple-800 dark:text-purple-100
                                                @elseif($user->role === 'hk') bg-teal-100 text-teal-800 dark:bg-teal-800 dark:text-teal-100
                                                @elseif($user->role === 'online_ecommerce') bg-orange-100 text-orange-800 dark:bg-orange-800 dark:text-orange-100
                                                @else bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200 @endif
                                            ">
                                                {{ Str::title(str_replace('_', ' ', $user->role)) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                            {{ $user->property->name ?? '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                            {{ $user->created_at ? $user->created_at->isoFormat('D MMM YYYY, HH:mm') : '-' }}
                                        </td>
                                        @can('manage-data')
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                                <a href="{{ route('admin.users.edit', $user->id) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-200">Edit</a>
                                                
                                                @if(Auth::id() !== $user->id)
                                                    <form method="POST" action="{{ route('admin.users.destroy', $user->id) }}" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menonaktifkan pengguna {{ addslashes($user->name) }}? Pengguna ini tidak akan bisa login lagi tetapi datanya tetap ada.');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-200">Nonaktifkan</button>
                                                    </form>
                                                @endif
                                            </td>
                                        @endcan
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="@can('manage-data') 6 @else 5 @endcan" class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-500 dark:text-gray-400">
                                            Tidak ada pengguna ditemukan.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $users->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>