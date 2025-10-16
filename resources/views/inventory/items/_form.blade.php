@csrf
<div class="space-y-4">
    
    <div>
        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Item</label>
        <input type="text" name="name" id="name" value="{{ old('name', $item->name ?? '') }}" required
               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
        @error('name')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="item_code" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Kode Item</label>
        @if (isset($item))
            {{-- Di halaman EDIT, tampilkan sebagai teks biasa yang tidak bisa diubah --}}
            <div class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-gray-100 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-400">
                {{ $item->item_code }}
            </div>
        @else
            {{-- Di halaman CREATE, tetap sebagai input readonly untuk diisi JavaScript --}}
            <input type="text" name="item_code" id="item_code" value="" readonly style="background-color: #e9ecef;"
                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-800 dark:border-gray-600 dark:text-gray-400">
        @endif
        @error('item_code')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="category_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Kategori</label>
        <select name="category_id" id="category_id" required
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
            <option value="">Pilih Kategori</option>
            @foreach($categories as $category)
                <option value="{{ $category->id }}" @selected(old('category_id', $item->category_id ?? '') == $category->id)>
                    {{ $category->name }}
                </option>
            @endforeach
        </select>
        @error('category_id')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="stock" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Stok</label>
        <input type="number" name="stock" id="stock" value="{{ old('stock', $item->stock ?? 0) }}" required
               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
        @error('stock')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="unit" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Unit (e.g., pcs, box, liter)</label>
        <input type="text" name="unit" id="unit" value="{{ old('unit', $item->unit ?? 'pcs') }}" required
               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
        @error('unit')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>
    
    <div>
        <label for="condition" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Kondisi</label>
        <select name="condition" id="condition" required
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
            <option value="baik" @selected(old('condition', $item->condition ?? 'baik') == 'baik')>Baik</option>
            <option value="rusak" @selected(old('condition', $item->condition ?? '') == 'rusak')>Rusak</option>
        </select>
        @error('condition')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="unit_price" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Harga Satuan (Opsional)</label>
        <input type="number" name="unit_price" id="unit_price" step="0.01" value="{{ old('unit_price', $item->unit_price ?? '') }}"
               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
        @error('unit_price')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="minimum_standard_quantity" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Kuantitas Standar Minimum (Opsional)</label>
        <input type="number" name="minimum_standard_quantity" id="minimum_standard_quantity" value="{{ old('minimum_standard_quantity', $item->minimum_standard_quantity ?? '') }}"
               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
        @error('minimum_standard_quantity')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="purchase_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal Pembelian (Opsional)</label>
        <input type="date" name="purchase_date" id="purchase_date" value="{{ old('purchase_date', isset($item) ? ($item->purchase_date ? $item->purchase_date->format('Y-m-d') : '') : '') }}"
               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
        @error('purchase_date')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>
</div>

<div class="flex items-center justify-end mt-6">
    <a href="{{ route('inventory.dashboard') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 mr-4">
        Batal
    </a>

    <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 active:bg-indigo-700 focus:outline-none focus:border-indigo-700 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
        {{ isset($item) ? 'Perbarui Item' : 'Simpan Item' }}
    </button>
</div>