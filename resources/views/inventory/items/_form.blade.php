<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div>
        <x-input-label for="name" :value="__('Nama Item')" />
        <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $item->name ?? '')" required autofocus />
    </div>
    <div>
        <x-input-label for="item_code" :value="__('Kode Item')" />
        <x-text-input id="item_code" class="block mt-1 w-full" type="text" name="item_code" :value="old('item_code', $item->item_code ?? '')" required />
    </div>
    <div>
        <x-input-label for="category_id" :value="__('Kategori')" />
        <select name="category_id" id="category_id" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm" required>
            <option value="">-- Pilih Kategori --</option>
            @foreach($categories as $category)
                <option value="{{ $category->id }}" @selected(old('category_id', $item->category_id ?? '') == $category->id)>
                    {{ $category->name }}
                </option>
            @endforeach
        </select>
    </div>
    <div>
        <x-input-label for="stock" :value="__('Stok Saat Ini')" />
        <x-text-input id="stock" class="block mt-1 w-full" type="number" name="stock" :value="old('stock', $item->stock ?? 0)" required />
    </div>
    <div>
        <x-input-label for="unit" :value="__('Satuan (cth: pcs, box, liter)')" />
        <x-text-input id="unit" class="block mt-1 w-full" type="text" name="unit" :value="old('unit', $item->unit ?? 'pcs')" required />
    </div>
    <div>
        <x-input-label for="condition" :value="__('Kondisi')" />
        <select name="condition" id="condition" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm" required>
            <option value="baik" @selected(old('condition', $item->condition ?? 'baik') == 'baik')>Baik</option>
            <option value="rusak" @selected(old('condition', $item->condition ?? '') == 'rusak')>Rusak</option>
        </select>
    </div>
    <div>
        <x-input-label for="unit_price" :value="__('Harga Satuan (Opsional)')" />
        <x-text-input id="unit_price" class="block mt-1 w-full" type="number" name="unit_price" :value="old('unit_price', $item->unit_price ?? 0)" />
    </div>
    <div>
        <x-input-label for="minimum_standard_quantity" :value="__('Stok Minimum (Opsional)')" />
        <x-text-input id="minimum_standard_quantity" class="block mt-1 w-full" type="number" name="minimum_standard_quantity" :value="old('minimum_standard_quantity', $item->minimum_standard_quantity ?? 0)" />
    </div>
    <div class="md:col-span-2">
        <x-input-label for="purchase_date" :value="__('Tanggal Pembelian (Opsional)')" />
        <x-text-input id="purchase_date" class="block mt-1 w-full" type="date" name="purchase_date" :value="old('purchase_date', $item->purchase_date ? \Carbon\Carbon::parse($item->purchase_date)->format('Y-m-d') : '')" />
    </div>
</div>

<div class="flex items-center justify-end mt-6">
    <a href="{{ route('inventory.dashboard') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline mr-4">Batal</a>
    <x-primary-button>
        {{ isset($item) ? __('Update Item') : __('Simpan Item') }}
    </x-primary-button>
</div>