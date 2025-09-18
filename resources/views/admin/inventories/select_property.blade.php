<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Pilih Properti untuk Dikelola') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-medium mb-6">Silakan pilih properti yang inventarisnya ingin Anda lihat atau kelola.</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @forelse ($properties as $property)
                            <a href="{{ route('admin.inventories.index', $property) }}" class="block p-6 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg shadow hover:bg-gray-100 dark:hover:bg-gray-600 transition">
                                <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">{{ $property->name }}</h5>
                                <p class="font-normal text-gray-700 dark:text-gray-400">{{ $property->address ?? 'Alamat tidak tersedia' }}</p>
                            </a>
                        @empty
                            <p class="col-span-full text-center text-gray-500">Tidak ada properti yang tersedia. Silakan tambahkan properti terlebih dahulu.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>