@csrf

{{-- Nama Item & Spesifikasi --}}
<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
    <div>
        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Item</label>
        <input type="text" name="name" id="name" value="{{ old('name', $inventory->name ?? '') }}"
               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"
               required>
        @error('name')
            <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
        @enderror
    </div>
    <div>
        <label for="specification" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Spesifikasi (Merek/Ukuran/Lainnya)</label>
        <input type="text" name="specification" id="specification" value="{{ old('specification', $inventory->specification ?? '') }}"
               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
        @error('specification')
            <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
        @enderror
    </div>
</div>

{{-- Kategori --}}
<div class="mb-4">
    <label for="category_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Kategori</label>
    <select name="category_id" id="category_id"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"
            required {{ $inventoryCategories->isEmpty() ? 'disabled' : '' }}>
        
        @if($inventoryCategories->isEmpty())
            <option value="">-- Buat Kategori Terlebih Dahulu --</option>
        @else
            <option value="">Pilih Kategori</option>
            @foreach($inventoryCategories as $category)
                <option value="{{ $category->id }}" {{ (old('category_id', $inventory->category_id ?? '') == $category->id) ? 'selected' : '' }}>
                    {{ $category->name }} ({{$category->category_code}})
                </option>
            @endforeach
        @endif
    </select>
    @if($inventoryCategories->isEmpty())
        <p class="text-sm text-yellow-600 mt-2">
            Anda belum membuat kategori inventaris. Silakan <a href="{{ route('admin.categories.create') }}" class="font-bold underline hover:text-yellow-500">buat kategori baru</a> sebelum menambahkan item.
        </p>
    @endif
    @error('category_id')
        <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
    @enderror
</div>

{{-- Detail Kuantitas & Harga --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
    <div>
        <label for="stock" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Stok Saat Ini</label>
        <input type="number" name="stock" id="stock" value="{{ old('stock', $inventory->stock ?? '0') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required min="0">
    </div>
    {{-- INPUT MSQ (BARU) --}}
    <div>
        <label for="minimum_standard_quantity" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Min. Standard Quantity (MSQ)</label>
        <input type="number" name="minimum_standard_quantity" id="minimum_standard_quantity" value="{{ old('minimum_standard_quantity', $inventory->minimum_standard_quantity ?? '0') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required min="0">
    </div>
    <div>
        <label for="unit_price" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Harga Satuan (Rp)</label>
        <input type="number" name="unit_price" id="unit_price" value="{{ old('unit_price', $inventory->unit_price ?? '0') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required min="0" placeholder="Contoh: 50000">
    </div>
</div>

{{-- Detail Lainnya --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
    <div>
        <label for="unit" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Satuan</label>
        <input type="text" name="unit" id="unit" value="{{ old('unit', $inventory->unit ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" placeholder="Cth: Pcs, Box, Kg" required>
    </div>
    {{-- INPUT TANGGAL PEMBELIAN (BARU) --}}
    <div>
        <label for="purchase_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal Pembelian</label>
        <input type="date" name="purchase_date" id="purchase_date" value="{{ old('purchase_date', $inventory->purchase_date ?? now()->format('Y-m-d')) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
    </div>
    <div>
        <label for="condition" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Kondisi</label>
        <select name="condition" id="condition" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
            <option value="baik" {{ (old('condition', $inventory->condition ?? '') == 'baik') ? 'selected' : '' }}>Baik</option>
            <option value="rusak" {{ (old('condition', $inventory->condition ?? '') == 'rusak') ? 'selected' : '' }}>Rusak</option>
        </select>
    </div>
</div>

<input type="hidden" name="property_id" value="{{ $inventory->property_id ?? request()->query('property_id') }}">