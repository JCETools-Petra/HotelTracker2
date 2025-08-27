<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Atur Tipe Kamar & Harga Dinamis') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $property->name }}</h3>
                <p class="text-gray-600 dark:text-gray-400 mt-1">Kelola semua tipe kamar dan aturan harganya di bawah ini.</p>
            </div>

            @if(session('success'))
                <div class="p-4 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-300 border border-green-300 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif
            @if($errors->any())
                <div class="p-4 bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-300 border border-red-300 rounded-lg">
                    <p class="font-bold">Terjadi kesalahan:</p>
                    <ul class="list-disc list-inside mt-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <h4 class="text-xl font-bold text-gray-800 dark:text-gray-200 mb-4">Tambah Tipe Kamar Baru</h4>
                <form action="{{ route('admin.pricing-rules.room-type.store', $property->id) }}" method="POST">
                    @csrf
                    <div class="flex items-end gap-4">
                        <div class="flex-grow">
                            <label for="name" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Nama Tipe Kamar</label>
                            <input type="text" name="name" id="name" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm">
                        </div>
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                            + Tambah
                        </button>
                    </div>
                </form>
            </div>
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <h4 class="text-xl font-bold text-gray-800 dark:text-gray-200 mb-4">Atur Kapasitas Bar (Berlaku untuk Semua Tipe Kamar)</h4>
                <form action="{{ route('admin.pricing-rules.property-bars.update', $property->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="grid grid-cols-2 md:grid-cols-5 gap-6">
                        <div>
                            <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">Kapasitas Bar 1</label>
                            <input type="number" name="bar_1" value="{{ old('bar_1', $property->bar_1) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm">
                        </div>
                        <div>
                            <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">Kapasitas Bar 2</label>
                            <input type="number" name="bar_2" value="{{ old('bar_2', $property->bar_2) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm">
                        </div>
                        <div>
                            <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">Kapasitas Bar 3</label>
                            <input type="number" name="bar_3" value="{{ old('bar_3', $property->bar_3) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm">
                        </div>
                        <div>
                            <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">Kapasitas Bar 4</label>
                            <input type="number" name="bar_4" value="{{ old('bar_4', $property->bar_4) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm">
                        </div>
                        <div>
                            <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">Kapasitas Bar 5</label>
                            <input type="number" name="bar_5" value="{{ old('bar_5', $property->bar_5) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm">
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end">
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                            Simpan Kapasitas Bar
                        </button>
                    </div>
                </form>
            </div>

            @if($property->roomTypes->isNotEmpty())
                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                    <div class="mb-6">
                        <label for="room_type_selector" class="block font-medium text-lg text-gray-700 dark:text-gray-300">Pilih Tipe Kamar untuk Diedit</label>
                        <select id="room_type_selector" class="mt-1 block w-full lg:w-1/2 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm">
                            @foreach($property->roomTypes as $roomType)
                                <option value="{{ $roomType->id }}">{{ $roomType->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div>
                        @foreach($property->roomTypes as $roomType)
                            <div id="form-container-{{ $roomType->id }}" class="room-type-form" style="display: none;">
                                @include('admin.pricing_rules.partials._form', ['roomType' => $roomType, 'property' => $property])
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg text-center">
                    <p class="text-gray-500">Belum ada tipe kamar. Silakan tambahkan terlebih dahulu.</p>
                </div>
            @endif
            

        </div>
    </div>
    
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const selector = document.getElementById('room_type_selector');
            const forms = document.querySelectorAll('.room-type-form');
            function toggleForms() {
                if (!selector) return;
                const selectedId = selector.value;
                forms.forEach(form => {
                    form.style.display = 'none';
                });
                const selectedForm = document.getElementById(`form-container-${selectedId}`);
                if (selectedForm) {
                    selectedForm.style.display = 'block';
                }
            }
            if(selector){
                selector.addEventListener('change', toggleForms);
                toggleForms();
            }
        });
    </script>
    @endpush
</x-app-layout>