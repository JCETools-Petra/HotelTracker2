<div class="space-y-4">
    {{-- Nama Item --}}
    <div>
        <x-input-label for="name" :value="__('Nama Item')" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $inventory->name ?? '')" required autofocus />
        <x-input-error class="mt-2" :messages="$errors->get('name')" />
    </div>

    {{-- Kategori --}}
    <div>
        <x-input-label for="category" :value="__('Kategori')" />
        <select id="category" name="category" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
            <option value="">Pilih Kategori</option>
            <option value="ROOM AMENITIES" @selected(old('category', $inventory->category ?? '') == 'ROOM AMENITIES')>ROOM AMENITIES</option>
            <option value="LINEN SUPPLY" @selected(old('category', $inventory->category ?? '') == 'LINEN SUPPLY')>LINEN SUPPLY</option>
            <option value="CLEANING SUPPLIES" @selected(old('category', $inventory->category ?? '') == 'CLEANING SUPPLIES')>CLEANING SUPPLIES</option>
        </select>
        <x-input-error class="mt-2" :messages="$errors->get('category')" />
    </div>

    {{-- Satuan --}}
    <div>
        <x-input-label for="unit" :value="__('Satuan (e.g., pcs, botol, pak)')" />
        <x-text-input id="unit" name="unit" type="text" class="mt-1 block w-full" :value="old('unit', $inventory->unit ?? '')" required />
        <x-input-error class="mt-2" :messages="$errors->get('unit')" />
    </div>

    {{-- Harga per Satuan --}}
    <div>
        <x-input-label for="price" :value="__('Harga per Satuan')" />
        <x-text-input id="price" name="price" type="number" step="0.01" class="mt-1 block w-full" :value="old('price', $inventory->price ?? '')" required placeholder="Contoh: 15000.00" />
        <x-input-error class="mt-2" :messages="$errors->get('price')" />
    </div>

    {{-- Jumlah / Stok --}}
    <div>
        <x-input-label for="quantity" :value="__('Jumlah (Stok Saat Ini)')" />
        <x-text-input id="quantity" name="quantity" type="number" class="mt-1 block w-full" :value="old('quantity', $inventory->quantity ?? '')" required />
        <x-input-error class="mt-2" :messages="$errors->get('quantity')" />
    </div>

    {{-- Deskripsi --}}
    <div>
        <x-input-label for="description" :value="__('Deskripsi (Opsional)')" />
        <textarea id="description" name="description" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" rows="3">{{ old('description', $inventory->description ?? '') }}</textarea>
        <x-input-error class="mt-2" :messages="$errors->get('description')" />
    </div>
</div>