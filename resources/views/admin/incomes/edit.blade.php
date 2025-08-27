<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Pendapatan Harian untuk ') }} {{ $income->property->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="POST" action="{{ route('admin.incomes.update', $income->id) }}">
                        @csrf
                        @method('PUT')
                        <div class="mb-6">
                            <x-input-label for="date" :value="__('Tanggal')" />
                            <x-text-input id="date" class="block mt-1 w-full md:w-1/2" type="date" name="date" :value="old('date', $date)" required autofocus />
                            <x-input-error :messages="$errors->get('date')" class="mt-2" />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                            
                            <div class="col-span-1 border-t pt-4"><h3 class="font-semibold text-lg">Kamar</h3></div>
                            <div class="col-span-1 border-t pt-4"><h3 class="font-semibold text-lg">Pendapatan (Rp)</h3></div>

                            <div>
                                <x-input-label for="offline_rooms" :value="__('Walk In Guest (Kamar)')" />
                                <x-text-input id="offline_rooms" class="block mt-1 w-full" type="number" name="offline_rooms" :value="old('offline_rooms', $income->offline_rooms)" />
                            </div>
                            <div>
                                <x-input-label for="offline_room_income" :value="__('Walk In Guest (Pendapatan)')" />
                                <x-text-input id="offline_room_income" class="block mt-1 w-full" type="number" name="offline_room_income" :value="old('offline_room_income', $income->offline_room_income)" />
                            </div>

                            <div>
                                <x-input-label for="online_rooms" :value="__('OTA (Kamar)')" />
                                <x-text-input id="online_rooms" class="block mt-1 w-full" type="number" name="online_rooms" :value="old('online_rooms', $income->online_rooms)" />
                            </div>
                            <div>
                                <x-input-label for="online_room_income" :value="__('OTA (Pendapatan)')" />
                                <x-text-input id="online_room_income" class="block mt-1 w-full" type="number" name="online_room_income" :value="old('online_room_income', $income->online_room_income)" />
                            </div>

                            <div>
                                <x-input-label for="ta_rooms" :value="__('TA/Travel Agent (Kamar)')" />
                                <x-text-input id="ta_rooms" class="block mt-1 w-full" type="number" name="ta_rooms" :value="old('ta_rooms', $income->ta_rooms)" />
                            </div>
                            <div>
                                <x-input-label for="ta_income" :value="__('TA/Travel Agent (Pendapatan)')" />
                                <x-text-input id="ta_income" class="block mt-1 w-full" type="number" name="ta_income" :value="old('ta_income', $income->ta_income)" />
                            </div>
                            
                            <div>
                                <x-input-label for="gov_rooms" :value="__('Gov/Government (Kamar)')" />
                                <x-text-input id="gov_rooms" class="block mt-1 w-full" type="number" name="gov_rooms" :value="old('gov_rooms', $income->gov_rooms)" />
                            </div>
                            <div>
                                <x-input-label for="gov_income" :value="__('Gov/Government (Pendapatan)')" />
                                <x-text-input id="gov_income" class="block mt-1 w-full" type="number" name="gov_income" :value="old('gov_income', $income->gov_income)" />
                            </div>
                            
                            <div>
                                <x-input-label for="corp_rooms" :value="__('Corp/Corporation (Kamar)')" />
                                <x-text-input id="corp_rooms" class="block mt-1 w-full" type="number" name="corp_rooms" :value="old('corp_rooms', $income->corp_rooms)" />
                            </div>
                            <div>
                                <x-input-label for="corp_income" :value="__('Corp/Corporation (Pendapatan)')" />
                                <x-text-input id="corp_income" class="block mt-1 w-full" type="number" name="corp_income" :value="old('corp_income', $income->corp_income)" />
                            </div>

                            <div>
                                <x-input-label for="compliment_rooms" :value="__('Compliment (Kamar)')" />
                                <x-text-input id="compliment_rooms" class="block mt-1 w-full" type="number" name="compliment_rooms" :value="old('compliment_rooms', $income->compliment_rooms)" />
                            </div>
                            <div>
                                <x-input-label for="compliment_income" :value="__('Compliment (Pendapatan)')" />
                                <x-text-input id="compliment_income" class="block mt-1 w-full" type="number" name="compliment_income" :value="old('compliment_income', $income->compliment_income)" />
                            </div>

                            <div>
                                <x-input-label for="house_use_rooms" :value="__('House Use (Kamar)')" />
                                <x-text-input id="house_use_rooms" class="block mt-1 w-full" type="number" name="house_use_rooms" :value="old('house_use_rooms', $income->house_use_rooms)" />
                            </div>
                            <div>
                                <x-input-label for="house_use_income" :value="__('House Use (Pendapatan)')" />
                                <x-text-input id="house_use_income" class="block mt-1 w-full" type="number" name="house_use_income" :value="old('house_use_income', $income->house_use_income)" />
                            </div>
                            <div>
                                <x-input-label for="afiliasi_rooms" :value="__('Afiliasi (Kamar)')" />
                                <x-text-input id="afiliasi_rooms" class="block mt-1 w-full" type="number" name="afiliasi_rooms" :value="old('afiliasi_rooms', $income->afiliasi_rooms ?? 0)" />
                                <x-input-error :messages="$errors->get('afiliasi_rooms')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="afiliasi_room_income" :value="__('Afiliasi (Pendapatan)')" />
                                <x-text-input id="afiliasi_room_income" class="block mt-1 w-full" type="number" name="afiliasi_room_income" :value="old('afiliasi_room_income', $income->afiliasi_room_income ?? 0)" />
                                <x-input-error :messages="$errors->get('afiliasi_room_income')" class="mt-2" />
                            </div>
                            <div class="col-span-full border-t pt-4"></div>
                            
                            <div class="col-span-1 md:col-start-2">
                                <x-input-label for="mice_income" :value="__('MICE (Pendapatan)')" />
                                <x-text-input id="mice_income" class="block mt-1 w-full" type="number" name="mice_income" :value="old('mice_income', $income->mice_income)" />
                            </div>
                            
                            <div class="col-span-1 md:col-start-2">
                                <x-input-label for="breakfast_income" :value="__('Breakfast (Pendapatan)')" />
                                <x-text-input id="breakfast_income" class="block mt-1 w-full" type="number" name="breakfast_income" :value="old('breakfast_income', $income->breakfast_income)" />
                            </div>

                            <div class="col-span-1 md:col-start-2">
                                <x-input-label for="lunch_income" :value="__('Lunch (Pendapatan)')" />
                                <x-text-input id="lunch_income" class="block mt-1 w-full" type="number" name="lunch_income" :value="old('lunch_income', $income->lunch_income)" />
                            </div>

                            <div class="col-span-1 md:col-start-2">
                                <x-input-label for="dinner_income" :value="__('Dinner (Pendapatan)')" />
                                <x-text-input id="dinner_income" class="block mt-1 w-full" type="number" name="dinner_income" :value="old('dinner_income', $income->dinner_income)" />
                            </div>
                            
                            <div class="col-span-1 md:col-start-2">
                                <x-input-label for="others_income" :value="__('Lainnya (Pendapatan)')" />
                                <x-text-input id="others_income" class="block mt-1 w-full" type="number" name="others_income" :value="old('others_income', $income->others_income)" />
                            </div>

                        </div>

                        <div class="flex items-center justify-end mt-6">
                             <a href="{{ route('admin.properties.show', $income->property_id) }}" class="text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                                {{ __('Batal') }}
                            </a>
                            <x-primary-button class="ml-4">
                                {{ __('Perbarui') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>