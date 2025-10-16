<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Kategori: ') . $category->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-bold mb-4">Formulir Edit Kategori</h3>

                    <form action="{{ route('inventory.categories.update', $category->id) }}" method="POST">
                        @method('PUT')
                        @include('inventory.categories._form')
                    </form>
                    
                </div>
            </div>
        </div>
    </div>

    {{-- [TAMBAHKAN SCRIPT DI BAWAH INI] --}}
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const nameInput = document.getElementById('name');
            const codeInput = document.getElementById('category_code');

            function generateCategoryCode(name) {
                if (!name) return '';
                const words = name.replace(/&/g, '').split(' ');
                let code = '';
                words.forEach(word => {
                    if (word) {
                        code += word.substring(0, 1).toUpperCase();
                    }
                });
                return code;
            }

            nameInput.addEventListener('input', function() {
                codeInput.value = generateCategoryCode(this.value);
            });
        });
    </script>
    @endpush
</x-app-layout>