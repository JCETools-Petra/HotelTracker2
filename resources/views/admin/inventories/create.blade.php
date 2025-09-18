<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{-- Tampilkan nama properti di header --}}
            Tambah Inventaris Baru untuk Properti: {{ $property->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">

                    {{-- ======================= PERBAIKAN DI SINI ======================= --}}
                    <form method="POST" action="{{ route('admin.inventories.store', $property) }}">
                        @csrf

                        {{-- Memuat file _form.blade.php yang berisi field input --}}
                        {{-- Pastikan variabel $inventory didefinisikan untuk form --}}
                        @include('admin.inventories._form', ['inventory' => new \App\Models\Inventory()])

                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('admin.inventories.index', $property) }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">
                                Batal
                            </a>
                            <x-primary-button>
                                {{ __('Simpan') }}
                            </x-primary-button>
                        </div>
                    </form>
                    {{-- ================================================================= --}}

                </div>
            </div>
        </div>
    </div>
</x-admin-layout>