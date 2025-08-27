<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Tampilan Harga BAR') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="mb-6">
                <form action="{{ route('ecommerce.bar-prices.index') }}" method="GET">
                    <div class="flex items-end gap-4">
                        <div class="flex-grow">
                            <label for="property_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Pilih Properti</label>
                            <select name="property_id" id="property_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300">
                                <option value="">-- Silakan Pilih --</option>
                                @foreach($properties as $property)
                                    <option value="{{ $property->id }}" {{ $selectedPropertyId == $property->id ? 'selected' : '' }}>
                                        {{ $property->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                                Tampilkan
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            @if($selectedProperty)
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <h3 class="text-lg font-semibold mb-4">
                            Harga untuk: <span class="text-indigo-500">{{ $selectedProperty->name }}</span>
                        </h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Tipe Kamar</th>
                                        @for ($i = 1; $i <= 5; $i++)
                                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                BAR {{ $i }}
                                            </th>
                                        @endfor
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @forelse ($roomTypePrices as $roomType)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap font-medium">{{ $roomType['name'] }}</td>
                                            @for ($i = 0; $i < 5; $i++)
                                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                                    @if(isset($roomType['prices'][$i]))
                                                        Rp {{ number_format($roomType['prices'][$i], 0, ',', '.') }}
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                            @endfor
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-6 py-8 text-center text-gray-500">
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
                <div class="text-center text-gray-500 dark:text-gray-400">
                    <p>Silakan pilih properti untuk menampilkan data harga BAR.</p>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>