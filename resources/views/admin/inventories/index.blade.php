<x-admin-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Bagian Header Halaman --}}
            <div class="flex flex-wrap justify-between items-center gap-4 mb-4">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-800 dark:text-gray-200">Inventaris untuk {{ $property->name }}</h1>
                    <a href="{{ route('admin.inventories.select') }}" class="text-sm text-indigo-600 hover:text-indigo-900">Kembali ke pemilihan properti</a>
                </div>
                
                {{-- [MODIFIKASI] Grup Tombol Aksi --}}
                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.inventories.create', ['property_id' => $property->id]) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md font-semibold text-xs uppercase hover:bg-indigo-700">
                        Tambah Item Baru
                    </a>
                    {{-- [TOMBOL BARU] Tombol Download Excel --}}
                    <a href="{{ route('admin.inventories.export', ['property_id' => $property->id]) }}" 
                       class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-500 active:bg-green-700 focus:outline-none focus:border-green-700 focus:ring ring-green-300">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        Download Excel
                    </a>
                </div>
            </div>

            {{-- Form Pencarian --}}
            <div class="mb-4">
                <form id="search-form" method="GET" action="{{ route('admin.inventories.index') }}">
                    <input type="hidden" name="property_id" value="{{ $property->id }}">
                    <div class="relative">
                        <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Cari nama item, kode, atau kategori..." class="w-full pl-4 pr-20 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <button type="submit" class="absolute inset-y-0 right-0 px-4 flex items-center bg-indigo-600 hover:bg-indigo-700 text-white rounded-r-lg">
                            Cari
                        </button>
                    </div>
                </form>
            </div>

            {{-- Notifikasi Sukses --}}
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded" role="alert">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Tabel Daftar Inventaris --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div id="inventory-table-container" class="p-6 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                    @include('admin.inventories._table_data')
                </div>
            </div>

            {{-- Legenda & Penjelasan --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mt-8">
                 <div class="p-6 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                     <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-200 border-b pb-3 dark:border-gray-700">
                         Penjelasan Kolom Aksi
                     </h3>
                     <ul class="mt-4 space-y-3 text-gray-700 dark:text-gray-300">
                         <li><span class="font-bold text-indigo-500">Edit:</span> Untuk mengubah detail item, seperti nama, stok, harga, atau kondisinya.</li>
                         <li><span class="font-bold text-red-500">Hapus:</span> Untuk menghapus item secara permanen dari daftar inventaris.</li>
                     </ul>
                 </div>

                 <div class="p-6 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                     <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-200 border-b pb-3 dark:border-gray-700">
                         Legenda Kode Kategori
                     </h3>
                     <ul class="mt-4 space-y-2">
                         @forelse($allCategories as $category)
                             <li class="flex items-center text-gray-700 dark:text-gray-300">
                                 <span class="font-bold w-20">{{ $category->category_code }}:</span>
                                 <span>{{ $category->name }}</span>
                             </li>
                         @empty
                             <li class="text-gray-500">Belum ada kategori yang dibuat.</li>
                         @endforelse
                     </ul>
                 </div>
            </div>
        </div>
    </div>

@push('scripts')
<script>
// Script AJAX Anda sudah bagus dan tidak perlu diubah, biarkan seperti ini.
document.addEventListener('DOMContentLoaded', function () {
    const tableContainer = document.getElementById('inventory-table-container');

    // Fungsi untuk mengambil data dan memperbarui tabel
    async function fetchData(url) {
        // Tampilkan indikator loading jika ada
        tableContainer.style.opacity = '0.5';
        
        try {
            const response = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            const html = await response.text();
            
            // Perbarui konten dan URL browser
            tableContainer.innerHTML = html;
            window.history.pushState({path: url}, '', url);
        } catch (error) {
            console.error('Gagal mengambil data:', error);
            // Tambahkan notifikasi error jika perlu
        } finally {
            // Hilangkan indikator loading
            tableContainer.style.opacity = '1';
        }
    }

    // Tangani event submit pada form pencarian
    const searchForm = document.getElementById('search-form');
    searchForm.addEventListener('submit', function (e) {
        e.preventDefault(); // Mencegah reload halaman
        const formData = new FormData(searchForm);
        const params = new URLSearchParams(formData);
        const url = `${searchForm.action}?${params.toString()}`;
        fetchData(url);
    });

    // Tangani klik pada link paginasi menggunakan event delegation
    document.addEventListener('click', function (e) {
        // Cek apakah yang diklik adalah link di dalam elemen paginasi
        if (e.target.matches('.pagination a') || e.target.closest('.pagination a')) {
            e.preventDefault();
            const link = e.target.matches('.pagination a') ? e.target : e.target.closest('.pagination a');
            const url = link.href;
            fetchData(url);
        }
    });
});
</script>
@endpush
</x-admin-layout>