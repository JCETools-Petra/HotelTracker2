<x-sales-layout>
    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight mb-4">
        {{ __('Tambah Booking Baru') }}
    </h2>

    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm">
        <form action="{{ route('sales.bookings.store') }}" method="POST">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Kolom Kiri --}}
                <div>
                    <div>
                        <x-input-label for="booking_date" :value="__('Tanggal Booking')" />
                        <x-text-input type="date" name="booking_date" id="booking_date" class="block mt-1 w-full" :value="old('booking_date', date('Y-m-d'))" required />
                        <x-input-error :messages="$errors->get('booking_date')" class="mt-2" />
                    </div>
                    <div class="mt-4">
                        <x-input-label for="client_name" :value="__('Nama Klien')" />
                        <x-text-input type="text" name="client_name" id="client_name" class="block mt-1 w-full" :value="old('client_name')" required />
                        <x-input-error :messages="$errors->get('client_name')" class="mt-2" />
                    </div>
                    <div class="mt-4">
                        <x-input-label for="mice_category_id" :value="__('Kategori MICE')" />
                        <select name="mice_category_id" id="mice_category_id" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm">
                            <option value="">-- Pilih Kategori (Opsional) --</option>
                            @foreach ($miceCategories as $category)
                                <option value="{{ $category->id }}" {{ old('mice_category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('mice_category_id')" class="mt-2" />
                    </div>
                    <div class="mt-4">
                        <x-input-label for="event_date" :value="__('Tanggal Acara')" />
                        <x-text-input type="date" name="event_date" id="event_date" class="block mt-1 w-full" :value="old('event_date')" required />
                         <x-input-error :messages="$errors->get('event_date')" class="mt-2" />
                    </div>
                     <div class="mt-4 grid grid-cols-2 gap-4">
                        <div>
                           <x-input-label for="start_time" :value="__('Jam Mulai')" />
                           <x-text-input type="time" name="start_time" id="start_time" class="block mt-1 w-full" :value="old('start_time')" required />
                           <x-input-error :messages="$errors->get('start_time')" class="mt-2" />
                        </div>
                        <div>
                           <x-input-label for="end_time" :value="__('Jam Selesai')" />
                           <x-text-input type="time" name="end_time" id="end_time" class="block mt-1 w-full" :value="old('end_time')" required />
                           <x-input-error :messages="$errors->get('end_time')" class="mt-2" />
                        </div>
                    </div>

                    {{-- ======================= AWAL BLOK YANG DIUBAH ======================= --}}
                    <div class="mt-4">
                        <x-input-label for="total_price" :value="__('Total Harga (Rp)')" />
                        <x-text-input type="number" name="total_price" id="total_price" class="block mt-1 w-full" :value="old('total_price', 0)" required placeholder="Contoh: 1500000" />
                        <x-input-error :messages="$errors->get('total_price')" class="mt-2" />
                    </div>
                    {{-- ======================= AKHIR BLOK YANG DIUBAH ====================== --}}
                </div>

                {{-- Kolom Kanan --}}
                <div>
                    <div>
                        <x-input-label for="participants" :value="__('Jumlah Peserta')" />
                        <x-text-input type="number" name="participants" id="participants" class="block mt-1 w-full" :value="old('participants')" required />
                        <x-input-error :messages="$errors->get('participants')" class="mt-2" />
                    </div>
                    <div class="mt-4">
                        <x-input-label :value="__('Hotel')" />
                        <x-text-input type="text" :value="$property->name" class="mt-1 block w-full bg-gray-100 dark:bg-gray-700" readonly />
                    </div>
                    <div class="mt-4">
                        <x-input-label for="room_id" :value="__('Ruang yang Digunakan')" />
                        <select id="room_id" name="room_id" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
                            <option value="">-- Pilih Ruangan --</option>
                            @foreach($rooms as $room)
                                <option value="{{ $room->id }}" {{ old('room_id') == $room->id ? 'selected' : '' }}>
                                    {{ $room->name }} @if($room->capacity)(Kapasitas: {{ $room->capacity }})@endif
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('room_id')" class="mt-2" />
                    </div>
                    <div class="mt-4">
                        <x-input-label for="person_in_charge" :value="__('Penanggung Jawab Acara')" />
                        <x-text-input type="text" name="person_in_charge" id="person_in_charge" class="block mt-1 w-full" :value="old('person_in_charge')" required />
                        <x-input-error :messages="$errors->get('person_in_charge')" class="mt-2" />
                    </div>
                    <div class="mt-4">
                        <x-input-label for="status" :value="__('Status')" />
                        <select name="status" id="status" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm" required>
                            <option value="Booking Sementara" @selected(old('status') == 'Booking Sementara')>Booking Sementara</option>
                            <option value="Booking Pasti" @selected(old('status') == 'Booking Pasti')>Booking Pasti</option>
                            <option value="Cancel" @selected(old('status') == 'Cancel')>Cancel</option>
                        </select>
                        <x-input-error :messages="$errors->get('status')" class="mt-2" />
                    </div>
                     <div class="mt-4">
                        <x-input-label for="notes" :value="__('Catatan Khusus')" />
                        <textarea name="notes" id="notes" rows="3" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm">{{ old('notes') }}</textarea>
                        <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end mt-6">
                <a href="{{ route('sales.bookings.index') }}" class="text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100 mr-4">Batal</a>
                <x-primary-button>
                    {{ __('Simpan Booking') }}
                </x-primary-button>
            </div>
        </form>
    </div>
</x-sales-layout>