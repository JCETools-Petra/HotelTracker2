@csrf
<div class="space-y-4">

    <div>
        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Kategori</label>
        <input type="text" name="name" id="name" value="{{ old('name', $category->name ?? '') }}" required
               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
        @error('name')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="category_code" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Kode Kategori</label>
        {{-- [PERBAIKAN] Selalu gunakan input readonly agar bisa diupdate oleh JS --}}
        <input type="text" name="category_code" id="category_code" value="{{ old('category_code', $category->category_code ?? '') }}" readonly style="background-color: #e9ecef;"
               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-800 dark:border-gray-600 dark:text-gray-400">
        @error('category_code')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>
</div>

<div class="flex items-center justify-end mt-6">
    <a href="{{ route('inventory.categories.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 mr-4">
        Batal
    </a>

    <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 active:bg-indigo-700 focus:outline-none focus:border-indigo-700 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
        {{ isset($category) ? 'Perbarui Kategori' : 'Simpan Kategori' }}
    </button>
</div>