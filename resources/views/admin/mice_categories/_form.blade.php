{{-- Nama Kategori --}}
<div>
    <x-input-label for="name" :value="__('Nama Kategori')" />
    <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $miceCategory->name ?? '')" required autofocus />
    <x-input-error :messages="$errors->get('name')" class="mt-2" />
</div>

{{-- Deskripsi --}}
<div class="mt-4">
    <x-input-label for="description" :value="__('Deskripsi (Opsional)')" />
    <textarea id="description" name="description" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">{{ old('description', $miceCategory->description ?? '') }}</textarea>
    <x-input-error :messages="$errors->get('description')" class="mt-2" />
</div>

<div class="flex items-center justify-end mt-6">
    <a href="{{ route('admin.mice-categories.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline mr-4">
        Batal
    </a>
    <x-primary-button>
        {{ isset($miceCategory) ? 'Update Kategori' : 'Simpan Kategori' }}
    </x-primary-button>
</div>
