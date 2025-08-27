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
                            
                            <!-- Nama Aplikasi -->
                            <div>
                                <x-input-label for="app_name" :value="__('Nama Aplikasi')" />
                                <x-text-input id="app_name" name="app_name" type="text" class="mt-1 block w-full" :value="old('app_name', $settings['app_name']->value ?? '')" required autofocus />
                                <x-input-error class="mt-2" :messages="$errors->get('app_name')" />
                            </div>

                            <!-- Logo Aplikasi -->
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

                            <!-- ============================================== -->
                            <!-- >> AWAL: Form Input Baru untuk Favicon << -->
                            <!-- ============================================== -->
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
                            <!-- ============================================== -->
                            <!-- >> AKHIR: Form Input Baru untuk Favicon <<  -->
                            <!-- ============================================== -->

                            <!-- Ukuran Logo -->
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

                            <div class="flex items-center gap-4 mt-6">
                                <x-primary-button>{{ __('Simpan Pengaturan') }}</x-primary-button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
