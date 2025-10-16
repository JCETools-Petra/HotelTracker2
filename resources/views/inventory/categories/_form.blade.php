<div>
    <x-input-label for="name" :value="__('Nama Kategori')" />
    <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $category->name ?? '')" required autofocus />
</div>

<div class="mt-4">
    <x-input-label for="category_code" :value="__('Kode Kategori (Singkat, cth: ATK)')" />
    <x-text-input id="category_code" class="block mt-1 w-full" type="text" name="category_code" :value="old('category_code', $category->category_code ?? '')" required />
</div>

<div class="flex items-center justify-end mt-6">
    <a href="{{ route('inventory.categories.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline mr-4">Batal</a>
    <x-primary-button>
        {{ isset($category) ? __('Update') : __('Simpan') }}
    </x-primary-button>
</div>