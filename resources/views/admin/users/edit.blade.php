<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Pengguna') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 md:p-8">

                    {{-- Komponen Notifikasi Baru --}}
                    <x-notification />

                    <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        {{-- Nama --}}
                        <div>
                            <x-input-label for="name" :value="__('Nama')" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $user->name)" required autofocus />
                        </div>

                        {{-- Email --}}
                        <div class="mt-4">
                            <x-input-label for="email" :value="__('Email')" />
                            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email', $user->email)" required />
                        </div>

                        {{-- Peran (Role) --}}
                        <div class="mt-4">
                            <x-input-label for="role" :value="__('Peran (Role)')" />
                            <select name="role" id="role" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
                                <option value="">-- Pilih Peran --</option>
                                @foreach($roles as $roleValue => $roleLabel)
                                    <option value="{{ $roleValue }}" @selected(old('role', $user->role) == $roleValue)>
                                        {{ $roleLabel }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Pilihan Properti --}}
                        <div class="mt-4" id="property-select-container" style="display: none;">
                            <x-input-label for="property_id" :value="__('Properti yang Dikelola')" />
                            <select name="property_id" id="property_id" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                <option value="">-- Pilih Properti --</option>
                                @foreach($properties as $property)
                                    <option value="{{ $property->id }}" @selected(old('property_id', $user->property_id) == $property->id)>
                                        {{ $property->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        {{-- Password --}}
                        <div class="mt-4">
                            <x-input-label for="password" :value="__('Password (Kosongkan jika tidak ingin mengubah)')" />
                            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" autocomplete="new-password" />
                        </div>
                        
                        {{-- Konfirmasi Password --}}
                        <div class="mt-4">
                            <x-input-label for="password_confirmation" :value="__('Konfirmasi Password')" />
                            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" autocomplete="new-password" />
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('admin.users.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline mr-4">Batal</a>
                            <x-primary-button>
                                {{ __('Update Pengguna') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const roleSelect = document.getElementById('role');
            const propertySelectContainer = document.getElementById('property-select-container');
            const propertySelect = document.getElementById('property_id');
            
            const rolesRequiringProperty = ['pengguna_properti', 'sales', 'online_ecommerce', 'hk', 'inventaris'];

            function togglePropertySelect() {
                const selectedRole = roleSelect.value;
                if (rolesRequiringProperty.includes(selectedRole)) {
                    propertySelectContainer.style.display = 'block';
                    propertySelect.required = true;
                } else {
                    propertySelectContainer.style.display = 'none';
                    propertySelect.required = false;
                    propertySelect.value = ''; // Selalu kosongkan jika tidak diperlukan
                }
            }
        
            // Panggil saat halaman dimuat
            togglePropertySelect();

            // Panggil setiap kali pilihan peran berubah
            roleSelect.addEventListener('change', togglePropertySelect);
        });
    </script>
    @endpush
</x-app-layout>