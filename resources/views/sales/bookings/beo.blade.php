<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Banquet Event Order') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
             <form action="{{ route('sales.bookings.storeBeo', $booking) }}" method="POST">
                @csrf
                <div class="bg-white dark:bg-gray-800 p-8 rounded-lg shadow-lg">
                    {{-- HEADER BEO --}}
                    <div class="flex justify-between items-center mb-6 pb-6 border-b dark:border-gray-700">
                         <div>
                            <p class="text-gray-600 dark:text-gray-400"><strong>Account Name:</strong> {{ $booking->client_name }}</p>
                            <p class="text-gray-600 dark:text-gray-400"><strong>Person Contact:</strong> {{ $booking->person_in_charge }}</p>
                            <div class="mt-2">
                                <label for="contact_phone" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Telp:</label>
                                <input type="text" id="contact_phone" name="contact_phone" value="{{ old('contact_phone', $beo->contact_phone) }}" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm text-sm">
                                @error('contact_phone')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-gray-600 dark:text-gray-400"><strong>BEO No:</strong> {{ $beo->beo_number ?? 'Auto-generated' }}</p>
                            <p class="text-gray-600 dark:text-gray-400"><strong>Dealed By:</strong> {{ $beo->dealed_by ?? auth()->user()->name }}</p>
                            <p class="text-gray-600 dark:text-gray-400"><strong>Date Event:</strong> {{ \Carbon\Carbon::parse($booking->event_date)->format('d-M-y') }}</p>
                        </div>
                    </div>

                    {{-- DETAIL ACARA --}}
                    <h3 class="text-lg font-semibold mb-3 dark:text-gray-200">Event Details</h3>
                    <div class="mb-6 bg-gray-50 dark:bg-gray-700/50 p-4 rounded-md">
                        <div class="mb-3">
                            <label for="room_setup" class="block font-medium text-sm text-gray-700 dark:text-gray-300">General Setup</label>
                            <select name="room_setup" id="room_setup" class="input-dynamic block mt-1 w-full md:w-1/3 text-sm" required>
                                @php $setup = old('room_setup', $beo->room_setup); @endphp
                                <option value="Classroom" @selected($setup == 'Classroom')>Classroom</option>
                                <option value="Theatre" @selected($setup == 'Theatre')>Theatre</option>
                                <option value="U-shape" @selected($setup == 'U-shape')>U-shape</option>
                                <option value="Round Table" @selected($setup == 'Round Table')>Round Table</option>
                                <option value="Lainnya" @selected($setup == 'Lainnya')>Lainnya</option>
                            </select>
                             @error('room_setup')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                        </div>
                        <div id="segments-container" class="dynamic-container">
                            @forelse (old('event_segments', $beo->event_segments ?? [['time' => '', 'event' => '', 'room' => $booking->property->name, 'attend' => $booking->participants, 'remark' => 'pax']]) as $index => $item)
                                <div class="grid grid-cols-12 gap-2 mb-2 dynamic-item">
                                    <div class="col-span-2"><input type="text" name="event_segments[{{$index}}][time]" class="input-dynamic" placeholder="13:00-17:00" value="{{ $item['time'] ?? '' }}"></div>
                                    <div class="col-span-3"><input type="text" name="event_segments[{{$index}}][event]" class="input-dynamic" placeholder="Nama Sesi Acara" value="{{ $item['event'] ?? '' }}"></div>
                                    <div class="col-span-3"><input type="text" name="event_segments[{{$index}}][room]" class="input-dynamic" placeholder="Nama Ruangan" value="{{ $item['room'] ?? '' }}"></div>
                                    <div class="col-span-1"><input type="number" name="event_segments[{{$index}}][attend]" class="input-dynamic text-center" placeholder="Pax" value="{{ $item['attend'] ?? '' }}"></div>
                                    <div class="col-span-2"><input type="text" name="event_segments[{{$index}}][remark]" class="input-dynamic" placeholder="Remark" value="{{ $item['remark'] ?? '' }}"></div>
                                    <div class="col-span-1"><button type="button" class="btn-remove">-</button></div>
                                </div>
                            @empty
                            @endforelse
                        </div>
                        <button type="button" class="btn-add" data-container="segments-container" data-template="segment-template">+ Add Event Segment</button>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        {{-- PERLENGKAPAN --}}
                        <div>
                            <h3 class="text-lg font-semibold mb-3 dark:text-gray-200">Banquet Setup (Perlengkapan)</h3>
                             <div class="mb-6 bg-gray-50 dark:bg-gray-700/50 p-4 rounded-md">
                                <div id="equipment-container" class="dynamic-container">
                                    @forelse (old('equipment_details', $beo->equipment_details ?? [['item' => '', 'qty' => '', 'remark' => '']]) as $index => $item)
                                    <div class="grid grid-cols-12 gap-2 mb-2 dynamic-item">
                                        <div class="col-span-5"><input type="text" name="equipment_details[{{$index}}][item]" class="input-dynamic" placeholder="Nama Barang" value="{{ $item['item'] ?? '' }}"></div>
                                        <div class="col-span-2"><input type="number" name="equipment_details[{{$index}}][qty]" class="input-dynamic text-center" placeholder="Jml" value="{{ $item['qty'] ?? '' }}"></div>
                                        <div class="col-span-4"><input type="text" name="equipment_details[{{$index}}][remark]" class="input-dynamic" placeholder="Keterangan" value="{{ $item['remark'] ?? '' }}"></div>
                                        <div class="col-span-1"><button type="button" class="btn-remove">-</button></div>
                                    </div>
                                    @empty
                                    @endforelse
                                </div>
                                <button type="button" class="btn-add" data-container="equipment-container" data-template="equipment-template">+ Add Equipment</button>
                            </div>
                        </div>

                        {{-- MENU --}}
                        <div>
                            <h3 class="text-lg font-semibold mb-3 dark:text-gray-200">Menu Details</h3>
                            <div class="mb-6 bg-gray-50 dark:bg-gray-700/50 p-4 rounded-md">
                                <div id="menu-container" class="dynamic-container">
                                    @forelse (old('menu_details', $beo->menu_details ?? [['type' => '', 'description' => '']]) as $index => $item)
                                    <div class="grid grid-cols-12 gap-2 mb-2 dynamic-item">
                                        <div class="col-span-4"><input type="text" name="menu_details[{{$index}}][type]" class="input-dynamic" placeholder="Tipe (cth: Coffee Break)" value="{{ $item['type'] ?? '' }}"></div>
                                        <div class="col-span-7"><textarea name="menu_details[{{$index}}][description]" class="input-dynamic" rows="1" placeholder="Detail Menu">{{ $item['description'] ?? '' }}</textarea></div>
                                        <div class="col-span-1"><button type="button" class="btn-remove">-</button></div>
                                    </div>
                                    @empty
                                    @endforelse
                                </div>
                                <button type="button" class="btn-add" data-container="menu-container" data-template="menu-template">+ Add Menu</button>
                            </div>
                        </div>
                    </div>

                    {{-- CATATAN DEPARTEMEN --}}
                    <h3 class="text-lg font-semibold mb-3 dark:text-gray-200">Information for All Departments</h3>
                    <div class="mb-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($departments as $dept)
                            <div>
                                <label for="dept-{{$dept}}" class="font-medium text-sm text-gray-700 dark:text-gray-300">{{$dept}}</label>
                                <textarea id="dept-{{$dept}}" name="department_notes[{{$dept}}]" class="input-dynamic" rows="2">{{ old('department_notes.'.$dept, $beo->department_notes[$dept] ?? '') }}</textarea>
                            </div>
                        @endforeach
                    </div>

                    {{-- BILLING (VERSI DROPDOWN) --}}
                    <h3 class="text-lg font-semibold mb-3 dark:text-gray-200">Billing Instruction</h3>
                    <div class="mb-6 bg-gray-50 dark:bg-gray-700/50 p-4 rounded-md">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label for="price_package_id" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Pilih Paket Harga</label>
                                <select id="price_package_id" name="price_package_id" class="input-dynamic" required>
                                    <option value="">-- Pilih Paket --</option>
                                    @foreach($pricePackages as $package)
                                        <option value="{{ $package->id }}" 
                                                data-price="{{ $package->price }}" 
                                                @selected(old('price_package_id', $beo->price_package_id) == $package->id)>
                                            {{ $package->name }} (Rp {{ number_format($package->price, 0, ',', '.') }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('price_package_id')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">Jumlah Peserta</label>
                                <input type="number" id="participants" value="{{ $booking->participants }}" class="input-dynamic text-right bg-gray-200 dark:bg-gray-800" disabled>
                            </div>
                            <div>
                                <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">Total Harga (Otomatis)</label>
                                <input type="text" id="total_price_display" class="input-dynamic text-right bg-gray-200 dark:bg-gray-800" disabled>
                            </div>
                        </div>
                    </div>
                    
                    {{-- CATATAN UMUM --}}
                     <div class="mb-6">
                        <label for="notes" class="block font-medium text-sm text-gray-700 dark:text-gray-300">General Notes</label>
                        <textarea name="notes" id="notes" rows="3" class="input-dynamic">{{ old('notes', $beo->notes) }}</textarea>
                    </div>

                    {{-- FOOTER --}}
                    <div class="flex items-center justify-end mt-8 border-t dark:border-gray-700 pt-6">
                        <button type="submit" class="px-6 py-2 bg-blue-600 text-white font-semibold rounded-md hover:bg-blue-700">
                            Save BEO
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- TEMPLATES FOR DYNAMIC JS --}}
    <template id="segment-template">
        <div class="grid grid-cols-12 gap-2 mb-2 dynamic-item">
            <div class="col-span-2"><input type="text" name="event_segments[__INDEX__][time]" class="input-dynamic" placeholder="13:00-17:00"></div>
            <div class="col-span-3"><input type="text" name="event_segments[__INDEX__][event]" class="input-dynamic" placeholder="Nama Sesi Acara"></div>
            <div class="col-span-3"><input type="text" name="event_segments[__INDEX__][room]" class="input-dynamic" placeholder="Nama Ruangan"></div>
            <div class="col-span-1"><input type="number" name="event_segments[__INDEX__][attend]" class="input-dynamic text-center" placeholder="Pax"></div>
            <div class="col-span-2"><input type="text" name="event_segments[__INDEX__][remark]" class="input-dynamic" placeholder="Remark"></div>
            <div class="col-span-1"><button type="button" class="btn-remove">-</button></div>
        </div>
    </template>
    <template id="equipment-template">
        <div class="grid grid-cols-12 gap-2 mb-2 dynamic-item">
            <div class="col-span-5"><input type="text" name="equipment_details[__INDEX__][item]" class="input-dynamic" placeholder="Nama Barang"></div>
            <div class="col-span-2"><input type="number" name="equipment_details[__INDEX__][qty]" class="input-dynamic text-center" placeholder="Jml"></div>
            <div class="col-span-4"><input type="text" name="equipment_details[__INDEX__][remark]" class="input-dynamic" placeholder="Keterangan"></div>
            <div class="col-span-1"><button type="button" class="btn-remove">-</button></div>
        </div>
    </template>
    <template id="menu-template">
        <div class="grid grid-cols-12 gap-2 mb-2 dynamic-item">
            <div class="col-span-4"><input type="text" name="menu_details[__INDEX__][type]" class="input-dynamic" placeholder="Tipe (cth: Coffee Break)"></div>
            <div class="col-span-7"><textarea name="menu_details[__INDEX__][description]" class="input-dynamic" rows="1" placeholder="Detail Menu"></textarea></div>
            <div class="col-span-1"><button type="button" class="btn-remove">-</button></div>
        </div>
    </template>

    <style>
        .input-dynamic { display: block; width: 100%; border-gray-300; dark:border-gray-700; dark:bg-gray-900; dark:text-gray-300; rounded-md; shadow-sm; font-size: 0.875rem; line-height: 1.25rem; }
        .btn-add { font-size: 0.875rem; line-height: 1.25rem; margin-top: 0.5rem; padding: 0.5rem 1rem; background-color: #e5e7eb; color: #374151; border-radius: 0.375rem; }
        .btn-add:hover { background-color: #d1d5db; }
        .btn-remove { padding: 0.5rem 0.75rem; background-color: #ef4444; color: white; border-radius: 0.375rem; width: 100%; }
        .btn-remove:hover { background-color: #dc2626; }
    </style>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // --- Logic untuk menambah/menghapus baris dinamis ---
        document.querySelectorAll('.btn-add').forEach(button => {
            button.addEventListener('click', function() {
                const containerId = this.dataset.container;
                const templateId = this.dataset.template;
                const container = document.getElementById(containerId);
                const template = document.getElementById(templateId).innerHTML;
                let index = Date.now(); 

                const newRowHtml = template.replace(/__INDEX__/g, index);
                container.insertAdjacentHTML('beforeend', newRowHtml);
            });
        });

        document.querySelectorAll('.dynamic-container').forEach(container => {
            container.addEventListener('click', function(e) {
                if (e.target && e.target.classList.contains('btn-remove')) {
                    e.target.closest('.dynamic-item').remove();
                }
            });
        });

        // --- Logic untuk kalkulasi harga otomatis ---
        const packageSelect = document.getElementById('price_package_id');
        const participantsInput = document.getElementById('participants');
        const totalDisplay = document.getElementById('total_price_display');
        const formatter = new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        });

        function calculateTotal() {
            if(!packageSelect) return; // Guard clause
            const selectedOption = packageSelect.options[packageSelect.selectedIndex];
            const price = parseFloat(selectedOption.dataset.price) || 0;
            const participants = parseInt(participantsInput.value) || 0;
            const total = price * participants;
            totalDisplay.value = formatter.format(total);
        }

        packageSelect.addEventListener('change', calculateTotal);
        
        // Hitung saat pertama kali halaman dimuat
        calculateTotal();
    });
    </script>
</x-app-layout>