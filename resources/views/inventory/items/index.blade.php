<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Manajemen Item Inventaris') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="mb-6 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-bold mb-2">Legenda Kategori</h3>
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-x-4 gap-y-1 text-sm">
                        @foreach ($categories as $category)
                            <div>
                                <span class="font-semibold">{{ $category->category_code }}:</span>
                                <span class="text-gray-600 dark:text-gray-400">{{ $category->name }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-bold">Daftar Item</h3>
                        <a href="{{ route('inventory.items.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 active:bg-indigo-700 focus:outline-none focus:border-indigo-700 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                            Tambah Item
                        </a>
                    </div>

                    @if (session('success'))
                        <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-lg">
                            {{ session('success') }}
                        </div>
                    @endif

                    <form id="search-form" method="GET" action="{{ route('inventory.dashboard') }}" class="mb-4">
                        <div class="flex">
                            <input type="text" name="search" id="search-input" placeholder="Cari item, kode, atau kategori..." value="{{ $search ?? '' }}"
                                   class="w-full border-gray-300 rounded-l-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                            <button type="submit" class="px-4 py-2 bg-indigo-600 border border-transparent rounded-r-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500">
                                Cari
                            </button>
                        </div>
                    </form>

                    <div id="item-table-container">
                        @include('inventory.items._table_data')
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const container = document.getElementById('item-table-container');
            const searchForm = document.getElementById('search-form');
            const searchInput = document.getElementById('search-input');

            // Fungsi untuk mengambil data dengan fetch
            const fetchData = async (url) => {
                try {
                    const response = await fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    const html = await response.text();
                    container.innerHTML = html;
                    // Update URL di browser tanpa reload
                    window.history.pushState({}, '', url);
                } catch (error) {
                    console.error('Error fetching data:', error);
                }
            };

            // Event listener untuk form pencarian
            searchForm.addEventListener('submit', function (e) {
                e.preventDefault();
                const url = new URL(this.action);
                url.searchParams.set('search', searchInput.value);
                fetchData(url.toString());
            });

            // Event listener untuk link paginasi (menggunakan event delegation)
            container.addEventListener('click', function (e) {
                if (e.target.tagName === 'A' && e.target.closest('.pagination')) {
                    e.preventDefault();
                    const url = e.target.href;
                    if(url) {
                        fetchData(url);
                    }
                }
            });

            // Handle back/forward browser buttons
            window.addEventListener('popstate', function () {
                fetchData(location.href);
            });
        });
    </script>
    @endpush
</x-app-layout>