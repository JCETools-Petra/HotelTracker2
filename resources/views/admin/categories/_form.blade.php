<div class="mb-4">
    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Kategori</label>
    <input type="text" name="name" id="name" value="{{ old('name', $category->name ?? '') }}"
           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"
           placeholder="Contoh: Alat Tulis Kantor"
           required>
    @error('name')
        <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
    @enderror
</div>