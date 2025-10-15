<x-admin-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h1 class="text-2xl font-semibold mb-4">Tambah Kategori Baru</h1>
                    <form action="{{ route('admin.categories.store') }}" method="POST">
                        @csrf
                        @include('admin.categories._form')
                        <div class="mt-4">
                            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Simpan</button>
                            <a href="{{ route('admin.categories.index') }}" class="px-4 py-2 bg-gray-300 text-black rounded-md hover:bg-gray-400 ml-2">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>