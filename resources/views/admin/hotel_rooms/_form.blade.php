{{-- Nomor Kamar --}}
<div>
    <x-input-label for="room_number" :value="__('Nomor Kamar')" />
    <x-text-input id="room_number" class="block mt-1 w-full" type="text" name="room_number" :value="old('room_number', $room->room_number ?? '')" required autofocus placeholder="Contoh: 101, 205, A-1" />
    <x-input-error :messages="$errors->get('room_number')" class="mt-2" />
</div>

{{-- Tipe Kamar --}}
<div class="mt-4">
    <x-input-label for="room_type_id" :value="__('Tipe Kamar')" />
    <select id="room_type_id" name="room_type_id" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm" required>
        <option value="">-- Pilih Tipe Kamar --</option>
        @foreach($roomTypes as $type)
            <option value="{{ $type->id }}" @selected(old('room_type_id', $room->room_type_id ?? '') == $type->id)>
                {{ $type->name }}
            </option>
        @endforeach
    </select>
    <x-input-error :messages="$errors->get('room_type_id')" class="mt-2" />
</div>

{{-- Kapasitas (Opsional) --}}
<div class="mt-4">
    <x-input-label for="capacity" :value="__('Kapasitas (Opsional)')" />
    <x-text-input id="capacity" class="block mt-1 w-full" type="number" name="capacity" :value="old('capacity', $room->capacity ?? '')" />
    <x-input-error :messages="$errors->get('capacity')" class="mt-2" />
</div>

{{-- Catatan (Opsional) --}}
<div class="mt-4">
    <x-input-label for="notes" :value="__('Catatan (Opsional)')" />
    <textarea id="notes" name="notes" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm">{{ old('notes', $room->notes ?? '') }}</textarea>
    <x-input-error :messages="$errors->get('notes')" class="mt-2" />
</div>

<div class="flex items-center justify-end mt-6">
    {{-- PERHATIKAN ROUTE ACTION DI SINI --}}
    <a href="{{ route('admin.properties.hotel-rooms.index', $property ?? $room->property) }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline mr-4">
        Batal
    </a>
    <x-primary-button>
        {{ isset($room) ? 'Update Kamar' : 'Simpan Kamar' }}
    </x-primary-button>
</div>