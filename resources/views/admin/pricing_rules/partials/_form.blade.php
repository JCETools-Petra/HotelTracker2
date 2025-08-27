<div class="border-t dark:border-gray-700 pt-6">
    <div class="flex justify-between items-center mb-4">
        <h4 class="text-xl font-bold text-gray-800 dark:text-gray-200">{{ $roomType->name }}</h4>
        <form action="{{ route('admin.pricing-rules.room-type.destroy', ['property' => $property->id, 'roomType' => $roomType->id]) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus tipe kamar ini?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="text-sm text-red-600 hover:text-red-900">Hapus Tipe Kamar</button>
        </form>
    </div>
    
    <form action="{{ route('admin.pricing-rules.rule.update', ['property' => $property->id, 'roomType' => $roomType->id]) }}" method="POST">
        @csrf
        @method('PUT')
        
        @php
            $rule = $roomType->pricingRule;
        @endphp

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div>
                <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">Bottom Rate (OTA)</label>
                <input type="number" name="bottom_rate" value="{{ old('bottom_rate', $rule->bottom_rate) }}" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm">
            </div>
            <div>
                <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">Publish Rate (OTA)</label>
                <input type="number" name="publish_rate" value="{{ old('publish_rate', $rule->publish_rate) }}" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm">
            </div>
            <div>
                <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">Persentase Kenaikan (%)</label>
                <input type="number" step="0.01" name="percentage_increase" value="{{ old('percentage_increase', $rule->percentage_increase) }}" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm">
            </div>
            <div>
                <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">Mulai Kenaikan dari Bar</label>
                <select name="starting_bar" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm">
                    @for ($i = 1; $i <= 5; $i++)
                        <option value="{{ $i }}" {{ old('starting_bar', $rule->starting_bar) == $i ? 'selected' : '' }}>Bar {{ $i }}</option>
                    @endfor
                </select>
            </div>
        </div>

        <div class="mt-6 flex justify-end">
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                Simpan Harga untuk {{ $roomType->name }}
            </button>
        </div>
    </form>
</div>