<x-admin-layout>
    <h2 class="font-semibold text-xl text-gray-800 leading-tight mb-4">Edit Paket Harga</h2>
    <div class="bg-white p-6 rounded-lg shadow-sm">
        <form action="{{ route('admin.price-packages.update', $pricePackage) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-4">
                <label for="name">Nama Paket</label>
                <input type="text" name="name" id="name" class="w-full border-gray-300 rounded-md" value="{{ $pricePackage->name }}" required>
            </div>
            <div class="mb-4">
                <label for="price">Harga (Rp)</label>
                <input type="number" name="price" id="price" class="w-full border-gray-300 rounded-md" value="{{ $pricePackage->price }}" required>
            </div>
            <div class="mb-4">
                <label for="description">Deskripsi</label>
                <textarea name="description" id="description" rows="3" class="w-full border-gray-300 rounded-md">{{ $pricePackage->description }}</textarea>
            </div>
            <div class="mb-4">
                <label class="inline-flex items-center">
                    <input type="checkbox" name="is_active" value="1" class="rounded border-gray-300" @checked($pricePackage->is_active)>
                    <span class="ml-2">Aktifkan paket ini</span>
                </label>
            </div>
            <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-md">Update</button>
        </form>
    </div>
</x-admin-layout>