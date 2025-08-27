<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Tambah Ruangan Baru untuk: ') . $property->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 md:p-8">
                    <form action="{{ route('admin.properties.rooms.store', $property) }}" method="POST">
                        @csrf
                        @include('admin.rooms._form')
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
