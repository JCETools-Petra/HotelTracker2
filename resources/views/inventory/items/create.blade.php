<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Tambah Item Inventaris Baru') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-bold mb-4">Formulir Item Baru</h3>
                    
                    <form action="{{ route('inventory.items.store') }}" method="POST">
                        @include('inventory.items._form')
                    </form>

                </div>
            </div>
        </div>
    </div>

    {{-- [TAMBAHKAN SCRIPT DI BAWAH INI] --}}
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const categorySelect = document.getElementById('category_id');
            const itemCodeInput = document.getElementById('item_code');
            
            // Data kategori dari controller
            const categories = @json($categoriesJson);

            categorySelect.addEventListener('change', function() {
                const selectedCategoryId = this.value;
                
                if (selectedCategoryId && categories[selectedCategoryId]) {
                    const categoryCode = categories[selectedCategoryId];
                    const randomPart = Math.random().toString(36).substring(2, 7).toUpperCase();
                    itemCodeInput.value = `${categoryCode}-${randomPart}`;
                } else {
                    itemCodeInput.value = '';
                }
            });
        });
    </script>
    @endpush
</x-app-layout>