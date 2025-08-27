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
                    <form action="{{ route('ecommerce.reservations.store') }}" method="POST">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="col-span-2">
                                <x-input-label for="guest_name" :value="__('Nama Tamu')" />
                                <x-text-input id="guest_name" class="block mt-1 w-full" type="text" name="guest_name" :value="old('guest_name')" required autofocus />
                                <x-input-error :messages="$errors->get('guest_name')" class="mt-2" />
                            </div>

                            <div class="col-span-2">
                                <x-input-label for="guest_email" :value="__('Email Tamu (Opsional)')" />
                                <x-text-input id="guest_email" class="block mt-1 w-full" type="email" name="guest_email" :value="old('guest_email')" />
                                <x-input-error :messages="$errors->get('guest_email')" class="mt-2" />
                            </div>
                            
                            <div>
                                <x-input-label for="checkin_date" :value="__('Tanggal Check-in')" />
                                <x-text-input id="checkin_date" class="block mt-1 w-full" type="date" name="checkin_date" :value="old('checkin_date')" required />
                                <x-input-error :messages="$errors->get('checkin_date')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="checkout_date" :value="__('Tanggal Check-out')" />
                                <x-text-input id="checkout_date" class="block mt-1 w-full" type="date" name="checkout_date" :value="old('checkout_date')" required />
                                <x-input-error :messages="$errors->get('checkout_date')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="property_id" :value="__('Pilih Properti')" />
                                <select name="property_id" id="property_id" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
                                    <option value="" disabled selected>-- Pilih Properti --</option>
                                    @foreach($properties as $property)
                                        <option value="{{ $property->id }}" {{ old('property_id') == $property->id ? 'selected' : '' }}>
                                            {{ $property->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('property_id')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="number_of_rooms" :value="__('Jumlah Kamar')" />
                                <x-text-input id="number_of_rooms" class="block mt-1 w-full" type="number" name="number_of_rooms" :value="old('number_of_rooms', 1)" required />
                                <x-input-error :messages="$errors->get('number_of_rooms')" class="mt-2" />
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('ecommerce.reservations.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 mr-4">
                                Batal
                            </a>
                            <x-primary-button>
                                {{ __('Simpan Reservasi') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>