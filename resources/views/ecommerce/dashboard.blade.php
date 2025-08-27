<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard Harga BAR') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Filter Properti --}}
            <div class="mb-8 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form action="{{ route('ecommerce.dashboard') }}" method="GET">
                        <div class="flex items-end gap-4">
                            <div class="flex-grow">
                                <label for="property_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Pilih Properti</label>
                                <select name="property_id" id="property_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300" onchange="this.form.submit()">
                                    <option value="">-- Pilih Properti --</option>
                                    @foreach($properties as $property)
                                        <option value="{{ $property->id }}" {{ $selectedPropertyId == $property->id ? 'selected' : '' }}>
                                            {{ $property->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            @if($selectedProperty)
                {{-- Info Okupansi (Sudah Disederhanakan) --}}
                <div class="mb-8 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="p-6 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg text-center">
                        <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Kamar Terisi Hari Ini</h4>
                        <p class="mt-1 text-3xl font-semibold text-gray-900 dark:text-white">{{ $currentOccupancyInfo['occupied_rooms'] }}</p>
                    </div>
                    <div class="p-6 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg text-center">
                        <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">BAR Aktif</h4>
                        <p class="mt-1 text-3xl font-semibold text-indigo-600 dark:text-indigo-400">BAR {{ $currentOccupancyInfo['active_bar'] }}</p>
                    </div>
                </div>

                {{-- Tabel Harga Berlaku --}}
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <h3 class="text-lg font-semibold mb-4">
                            Harga Berlaku untuk: <span class="text-indigo-500">{{ $selectedProperty->name }}</span>
                        </h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Tipe Kamar</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Harga BAR Berlaku
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @forelse ($roomTypePrices as $roomType)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap font-medium">{{ $roomType['name'] }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right font-semibold text-lg">
                                                Rp {{ number_format($roomType['active_price'], 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="px-6 py-8 text-center text-gray-500">
                                                Tidak ada tipe kamar yang dikonfigurasi untuk properti ini.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @else
                <div class="text-center py-16 text-gray-500 dark:text-gray-400">
                    <p>Silakan pilih properti untuk menampilkan data harga BAR.</p>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>