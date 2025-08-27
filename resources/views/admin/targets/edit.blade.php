<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Target Pendapatan') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="POST" action="{{ route('admin.revenue-targets.update', $revenueTarget->id) }}">
                        @csrf
                        @method('PUT')

                        <div class="mt-4">
                            <x-input-label for="property_id" :value="__('Properti')" />
                            <select id="property_id" name="property_id" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
                                <option value="">{{ __('-- Pilih Properti --') }}</option>
                                @foreach ($properties as $property)
                                    <option value="{{ $property->id }}" {{ old('property_id', $revenueTarget->property_id) == $property->id ? 'selected' : '' }}>
                                        {{ $property->name }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('property_id')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="month_year" :value="__('Periode Target (Bulan dan Tahun)')" />
                            {{-- Controller mengirimkan $revenueTarget->month_year_form yang sudah diformat Y-m --}}
                            <x-text-input id="month_year" class="block mt-1 w-full" type="month" name="month_year" :value="old('month_year', $revenueTarget->month_year_form ?? '')" required />
                            <x-input-error :messages="$errors->get('month_year')" class="mt-2" />
                             <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Format: YYYY-MM (misal: {{ date('Y-m') }})</p>
                        </div>

                        <div class="mt-4">
                            <x-input-label for="target_amount" :value="__('Target Pendapatan (Rp)')" />
                            <x-text-input id="target_amount" class="block mt-1 w-full" type="number" name="target_amount" :value="old('target_amount', $revenueTarget->target_amount)" required step="1000" min="0" />
                            <x-input-error :messages="$errors->get('target_amount')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-6">
                             <a href="{{ route('admin.revenue-targets.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-gray-800 dark:text-gray-200 uppercase tracking-widest hover:bg-gray-300 dark:hover:bg-gray-500 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150 mr-4">
                                {{ __('Batal') }}
                            </a>
                            <x-primary-button>
                                {{ __('Update') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>