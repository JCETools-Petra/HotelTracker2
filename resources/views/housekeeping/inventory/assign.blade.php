<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Tugaskan Amenities ke Kamar: ') }} {{ $room->room_number }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-xl font-semibold mb-4">{{ __('Inventaris Amenities') }}</h3>
                    
                    @if(session('error'))
                        <div class="mb-4 font-medium text-sm text-red-600 bg-red-100 dark:bg-red-900 dark:text-red-300 p-3 rounded-md border border-red-300 dark:border-red-700">
                            {{ session('error') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('housekeeping.inventory.update', $room->id) }}">
                        @csrf
                        {{-- Menggunakan grid: 1 kolom di mobile, 2 kolom di desktop --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4">
                            @foreach($inventories as $inventory)
                                <div>
                                    <div class="flex items-center justify-between">
                                        <label for="amenities[{{ $inventory->id }}][quantity]" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            {{ $inventory->name }} <span class="text-xs text-gray-500">(stok: {{ $inventory->quantity }})</span>
                                        </label>
                                        <input type="number" 
                                               name="amenities[{{ $inventory->id }}][quantity]" 
                                               value="{{ $currentAmenities->get($inventory->id)->pivot->quantity ?? 0 }}" 
                                               min="0" 
                                               class="w-24 form-input rounded-md shadow-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                                        >
                                    </div>
                                    <x-input-error :messages="$errors->get('amenities.' . $inventory->id . '.quantity')" class="mt-2" />
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-6 flex justify-end space-x-2">
                            <a href="{{ route('housekeeping.inventory.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-700 border border-transparent rounded-md font-semibold text-xs text-gray-800 dark:text-gray-200 uppercase tracking-widest hover:bg-gray-300 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                {{ __('Batal') }}
                            </a>
                            <x-primary-button>
                                {{ __('Simpan Perubahan') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>