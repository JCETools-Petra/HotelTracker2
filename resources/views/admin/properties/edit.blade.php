<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Properti') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form method="POST" action="{{ route('admin.properties.update', $property) }}">
                        @csrf
                        @method('PUT')

                        {{-- Nama Properti --}}
                        <div>
                            <label for="name">Nama Properti</label>
                            <input id="name" class="block mt-1 w-full" type="text" name="name" value="{{ old('name', $property->name) }}" required autofocus />
                        </div>

                        {{-- Alamat --}}
                        <div class="mt-4">
                            <label for="address">Alamat</label>
                            <input id="address" class="block mt-1 w-full" type="text" name="address" value="{{ old('address', $property->address) }}" />
                        </div>

                        {{-- ======================= TAMBAHKAN BLOK INI ======================= --}}
                        <div class="mt-4">
                            <label for="total_rooms">Jumlah Total Kamar</label>
                            <input id="total_rooms" class="block mt-1 w-full" type="number" name="total_rooms" value="{{ old('total_rooms', $property->total_rooms) }}" required />
                             @error('total_rooms')
                                <span class="text-red-500 text-sm mt-2">{{ $message }}</span>
                            @enderror
                        </div>
                        {{-- ==================================================================== --}}

                        {{-- Tombol Simpan --}}
                        <div class="flex items-center justify-end mt-4">
                            <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-md">
                                Perbarui
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>