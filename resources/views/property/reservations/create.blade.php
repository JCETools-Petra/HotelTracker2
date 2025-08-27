<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Tambah Reservasi Baru') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form action="{{ route('property.reservations.store') }}" method="POST">
                        @csrf
                        {{-- Input tersembunyi untuk property_id --}}
                        <input type="hidden" name="property_id" value="{{ $property->id }}">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="col-span-2">
                                <x-input-label for="property_name" :value="__('Properti')" />
                                <x-text-input id="property_name" class="block mt-1 w-full bg-gray-100 dark:bg-gray-700" type="text" value="{{ $property->name }}" readonly />
                            </div>

                            <div>
                                <x-input-label for="room_type_id" :value="__('Pilih Tipe Kamar')" />
                                <select name="room_type_id" id="room_type_id" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm" required>
                                    <option value="" disabled selected>-- Pilih Tipe Kamar --</option>
                                    @foreach($roomTypes as $roomType)
                                        <option value="{{ $roomType->id }}" {{ old('room_type_id') == $roomType->id ? 'selected' : '' }}>
                                            {{ $roomType->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('room_type_id')" class="mt-2" />
                            </div>
                            
                            <div>
                                <x-input-label for="display_price" :value="__('Harga BAR Aktif')" />
                                <x-text-input id="display_price" class="block mt-1 w-full bg-gray-100 dark:bg-gray-700" type="text" value="-" readonly />
                                <input type="hidden" name="final_price" id="final_price">
                            </div>

                            <div class="col-span-2"><hr class="dark:border-gray-600 mt-2"></div>
                            
                            <div class="col-span-2">
                                <x-input-label for="guest_name" :value="__('Nama Tamu')" />
                                <x-text-input id="guest_name" name="guest_name" :value="old('guest_name')" class="block mt-1 w-full" type="text" required />
                            </div>
                            
                            <div>
                                <x-input-label for="checkin_date" :value="__('Tanggal Check-in')" />
                                <x-text-input id="checkin_date" name="checkin_date" :value="old('checkin_date')" class="block mt-1 w-full" type="date" required />
                            </div>

                            <div>
                                <x-input-label for="checkout_date" :value="__('Tanggal Check-out')" />
                                <x-text-input id="checkout_date" name="checkout_date" :value="old('checkout_date')" class="block mt-1 w-full" type="date" required />
                            </div>

                             <div>
                                <x-input-label for="number_of_rooms" :value="__('Jumlah Kamar')" />
                                <x-text-input id="number_of_rooms" name="number_of_rooms" value="{{ old('number_of_rooms', 1) }}" class="block mt-1 w-full" type="number" required />
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('property.reservations.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 mr-4">Batal</a>
                            <x-primary-button>{{ __('Simpan') }}</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const roomTypeSelect = document.getElementById('room_type_id');
            const displayPrice = document.getElementById('display_price');
            const finalPrice = document.getElementById('final_price');
            
            const priceUrlTemplate = "{{ route('property.room-types.active-price', ['roomType' => 'ROOMTYPE_ID']) }}";

            roomTypeSelect.addEventListener('change', function () {
                const roomTypeId = this.value;
                displayPrice.value = 'Menghitung...';
                finalPrice.value = '';

                if (!roomTypeId) {
                    displayPrice.value = '-';
                    return;
                }
                
                const fetchUrl = priceUrlTemplate.replace('ROOMTYPE_ID', roomTypeId);
                
                fetch(fetchUrl)
                    .then(response => response.json())
                    .then(data => {
                        finalPrice.value = data.price;
                        displayPrice.value = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(data.price);
                    });
            });
        });
    </script>
    @endpush
</x-app-layout>