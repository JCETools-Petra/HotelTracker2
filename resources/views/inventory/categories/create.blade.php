<x-inventory-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Tambah Kategori Baru') }}
        </h2>
    </x-slot>

    <div class="max-w-4xl mx-auto">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <x-notification />
                <form action="{{ route('inventory.categories.store') }}" method="POST">
                    @csrf
                    @include('inventory.categories._form')
                </form>
            </div>
        </div>
    </div>
</x-inventory-layout>