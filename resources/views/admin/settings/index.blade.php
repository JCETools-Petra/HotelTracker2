<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Pengaturan Aplikasi') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form action="{{ route('admin.settings.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="space-y-6">
                            
                            <div>
                                <x-input-label for="app_name" :value="__('Nama Aplikasi')" />
                                <x-text-input id="app_name" name="app_name" type="text" class="mt-1 block w-full" :value="old('app_name', $settings['app_name']->value ?? '')" required autofocus />
                                <x-input-error class="mt-2" :messages="$errors->get('app_name')" />
                            </div>

                            <div class="mt-4">
                                <x-input-label for="logo_path" :value="__('Logo Aplikasi (Disarankan .png transparan)')" />
                                <div class="mt-2 flex items-center space-x-4">
                                    @if(isset($settings['logo_path']) && $settings['logo_path']->value)
                                        <img src="{{ asset('storage/' . $settings['logo_path']->value) }}" alt="Logo saat ini" class="h-16 w-16 bg-gray-200 dark:bg-gray-700 p-1 rounded-md object-contain">
                                    @endif
                                    <x-text-input id="logo_path" name="logo_path" type="file" class="block w-full" />
                                </div>
                                <x-input-error class="mt-2" :messages="$errors->get('logo_path')" />
                            </div>
                            
                            <div class="mt-4">
                                <x-input-label for="favicon_path" :value="__('Favicon (.png atau .ico)')" />
                                <div class="mt-2 flex items-center space-x-4">
                                    @if(isset($settings['favicon_path']) && $settings['favicon_path']->value)
                                        <img src="{{ asset('storage/' . $settings['favicon_path']->value) }}" alt="Favicon saat ini" class="h-8 w-8 bg-gray-200 dark:bg-gray-700 p-1 rounded-md object-contain">
                                    @endif
                                    <x-text-input id="favicon_path" name="favicon_path" type="file" class="block w-full" />
                                </div>
                                <p class="text-xs text-gray-500 mt-1">Unggah gambar kotak (misal: 32x32 atau 64x64 piksel) untuk hasil terbaik.</p>
                                <x-input-error class="mt-2" :messages="$errors->get('favicon_path')" />
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                                <div>
                                    <x-input-label for="logo_size" :value="__('Ukuran Logo Login (px)')" />
                                    <x-text-input id="logo_size" name="logo_size" type="number" class="mt-1 block w-full" :value="old('logo_size', $settings['logo_size']->value ?? '120')" />
                                    <p class="text-xs text-gray-500 mt-1">Tinggi logo dalam piksel di halaman login.</p>
                                    <x-input-error class="mt-2" :messages="$errors->get('logo_size')" />
                                </div>
                                <div>
                                    <x-input-label for="sidebar_logo_size" :value="__('Ukuran Logo Sidebar (px)')" />
                                    <x-text-input id="sidebar_logo_size" name="sidebar_logo_size" type="number" class="mt-1 block w-full" :value="old('sidebar_logo_size', $settings['sidebar_logo_size']->value ?? '75')" />
                                    <p class="text-xs text-gray-500 mt-1">Tinggi logo dalam piksel di sidebar.</p>
                                    <x-input-error class="mt-2" :messages="$errors->get('sidebar_logo_size')" />
                                </div>
                            </div>
                            
                            <div class="border-t border-gray-200 dark:border-gray-700 mt-6 pt-6">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                    {{ __('Pengaturan Notifikasi Stok Rendah') }}
                                </h3>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                    {{ __('Atur notifikasi email untuk barang yang stoknya di bawah jumlah minimum.') }}
                                </p>

                                <div class="mt-4">
                                    <x-input-label for="low_stock_notification" :value="__('Status Notifikasi')" />
                                    <select id="low_stock_notification" name="low_stock_notification" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                        <option value="1" {{ old('low_stock_notification', $settings['low_stock_notification']->value ?? 0) == 1 ? 'selected' : '' }}>
                                            {{ __('Aktifkan') }}
                                        </option>
                                        <option value="0" {{ old('low_stock_notification', $settings['low_stock_notification']->value ?? 0) == 0 ? 'selected' : '' }}>
                                            {{ __('Nonaktifkan') }}
                                        </option>
                                    </select>
                                    <x-input-error class="mt-2" :messages="$errors->get('low_stock_notification')" />
                                </div>

                                <div class="mt-4">
                                    <x-input-label for="low_stock_recipient_email" :value="__('Email Penerima Notifikasi')" />
                                    <x-text-input id="low_stock_recipient_email" name="low_stock_recipient_email" type="email" class="mt-1 block w-full" :value="old('low_stock_recipient_email', $settings['low_stock_recipient_email']->value ?? '')" placeholder="contoh@email.com" />
                                    <p class="text-xs text-gray-500 mt-1">Email yang akan menerima laporan stok rendah.</p>
                                    <x-input-error class="mt-2" :messages="$errors->get('low_stock_recipient_email')" />
                                </div>
                            </div>
                            <div class="flex items-center gap-4 mt-6">
                                <x-primary-button>{{ __('Simpan Pengaturan') }}</x-primary-button>
                            </div>
                        </div>
                    </form>
                    <div class="border-t border-gray-200 dark:border-gray-700 mt-6 pt-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                            Kirim Laporan Stok Rendah (MSQ)
                        </h3>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            Pilih properti untuk mengirimkan laporan semua barang yang stoknya di bawah MSQ ke email yang telah diatur.
                        </p>
                        
                        <form action="{{ route('admin.settings.testMsqEmail') }}" method="POST" class="mt-4">
                            @csrf
                            <div class="flex items-end gap-4">
                                {{-- Dropdown untuk memilih properti --}}
                                <div class="flex-grow">
                                    <x-input-label for="property_id" :value="__('Pilih Properti')" />
                                    <select id="property_id" name="property_id" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
                                        <option value="" disabled selected>-- Pilih salah satu properti --</option>
                                        @foreach ($properties as $property)
                                            <option value="{{ $property->id }}">{{ $property->name }}</option>
                                        @endforeach
                                    </select>
                                    <x-input-error class="mt-2" :messages="$errors->get('property_id')" />
                                </div>
                    
                                {{-- Tombol Kirim --}}
                                <div>
                                    <x-primary-button type="submit">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                                            <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                                        </svg>
                                        {{ __('Kirim Laporan') }}
                                    </x-primary-button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>