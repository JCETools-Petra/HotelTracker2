<div class="flex flex-col justify-between h-full">
    <div>
        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">{{ $property->name }}</h3>
        
        <div class="mt-4">
            <table class="w-full text-sm">
                <tbody class="text-gray-600 dark:text-gray-400">
                    @php
                        // Kalkulasi Total per Grup
                        $roomCategories = ['offline_room_income', 'online_room_income', 'ta_income', 'gov_income', 'corp_income', 'compliment_income', 'house_use_income', 'afiliasi_room_income'];
                        $fnbCategories = ['breakfast_income', 'lunch_income', 'dinner_income'];

                        $totalRoomRevenue = collect($roomCategories)->sum(fn($key) => $property->{'total_' . $key} ?? 0);
                        $totalFnbRevenue = collect($fnbCategories)->sum(fn($key) => $property->{'total_' . $key} ?? 0);
                        $totalMiceRevenue = $property->mice_revenue_breakdown->sum('total_mice_revenue') ?? 0;
                        
                        $grandTotal = $property->dailyRevenue ?? 0;

                        // Helper function untuk menghitung persentase
                        $getPercentage = fn($value, $total) => ($total > 0) ? '(' . number_format(($value / $total) * 100, 1, ',', '.') . '%)' : '';
                    @endphp

                    {{-- PENDAPATAN KAMAR --}}
                    <tr>
                        <td class="pt-3 pb-1 pr-4 font-semibold text-gray-500 dark:text-gray-400" colspan="2">
                            Pendapatan Kamar <span class="font-normal text-gray-400">{{ $getPercentage($totalRoomRevenue, $grandTotal) }}</span>
                        </td>
                    </tr>
                    @foreach (['offline_room_income' => 'Walk In', 'online_room_income' => 'OTA', 'ta_income' => 'Travel Agent', 'gov_income' => 'Government', 'corp_income' => 'Corporation', 'compliment_income' => 'Compliment', 'house_use_income' => 'House Use', 'afiliasi_room_income' => 'Afiliasi'] as $key => $label)
                        @php $value = $property->{'total_' . $key} ?? 0; @endphp
                        @if ($value > 0)
                        <tr>
                            <td class="py-1.5 pr-4 pl-4">{{ $label }}</td>
                            <td class="py-1.5 text-right font-medium text-gray-700 dark:text-gray-300">
                                <div class="flex flex-col items-end">
                                    <span>Rp {{ number_format($value, 0, ',', '.') }}</span>
                                    <span class="text-xs text-gray-400">{{ $getPercentage($value, $totalRoomRevenue) }}</span>
                                </div>
                            </td>
                        </tr>
                        @endif
                    @endforeach

                    {{-- PENDAPATAN F&B --}}
                    @if ($totalFnbRevenue > 0)
                    <tr class="border-t border-dashed border-gray-300 dark:border-gray-600">
                        <td class="pt-3 pb-1 pr-4 font-semibold text-gray-500 dark:text-gray-400" colspan="2">
                            Pendapatan F&B <span class="font-normal text-gray-400">{{ $getPercentage($totalFnbRevenue, $grandTotal) }}</span>
                        </td>
                    </tr>
                    @foreach (['breakfast_income' => 'Breakfast', 'lunch_income' => 'Lunch', 'dinner_income' => 'Dinner'] as $key => $label)
                        @php $value = $property->{'total_' . $key} ?? 0; @endphp
                        @if ($value > 0)
                        <tr>
                            <td class="py-1.5 pr-4 pl-4">{{ $label }}</td>
                            <td class="py-1.5 text-right font-medium text-gray-700 dark:text-gray-300">
                                <div class="flex flex-col items-end">
                                    <span>Rp {{ number_format($value, 0, ',', '.') }}</span>
                                    <span class="text-xs text-gray-400">{{ $getPercentage($value, $totalFnbRevenue) }}</span>
                                </div>
                            </td>
                        </tr>
                        @endif
                    @endforeach
                    @endif
                    
                    <tr class="border-t border-dashed border-gray-300 dark:border-gray-600">
                        <td class="pt-3 pb-1 pr-4 font-semibold text-gray-500 dark:text-gray-400" colspan="2">
                            Detail Kamar
                        </td>
                    </tr>
                    <tr>
                        <td class="py-1.5 pr-4 pl-4">Kamar OTA</td>
                        <td class="py-1.5 text-right font-bold text-gray-700 dark:text-gray-300">
                            {{ number_format($property->total_online_rooms ?? 0, 0, ',', '.') }}
                        </td>
                    </tr>
                    <tr>
                        <td class="py-1.5 pr-4 pl-4">Kamar Afiliasi</td>
                        <td class="py-1.5 text-right font-bold text-gray-700 dark:text-gray-300">
                            {{ number_format($property->total_afiliasi_rooms ?? 0, 0, ',', '.') }}
                        </td>
                    </tr>
                    {{-- PENDAPATAN MICE --}}
                    @if($totalMiceRevenue > 0)
                        <tr class="border-t border-dashed border-gray-300 dark:border-gray-600">
                             <td class="pt-3 pb-1 pr-4 font-semibold text-gray-500 dark:text-gray-400" colspan="2">
                                 Pendapatan MICE <span class="font-normal text-gray-400">{{ $getPercentage($totalMiceRevenue, $grandTotal) }}</span>
                            </td>
                        </tr>
                        @foreach($property->mice_revenue_breakdown as $mice)
                            @php $value = $mice->total_mice_revenue; @endphp
                            <tr>
                                <td class="py-1.5 pr-4 pl-4">{{ $mice->miceCategory->name ?? 'Lainnya' }}</td>
                                <td class="py-1.5 text-right font-medium text-gray-700 dark:text-gray-300">
                                    <div class="flex flex-col items-end">
                                        <span>Rp {{ number_format($value, 0, ',', '.') }}</span>
                                        <span class="text-xs text-gray-400">{{ $getPercentage($value, $totalMiceRevenue) }}</span>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @endif

                    {{-- PENDAPATAN LAINNYA --}}
                    @if (($property->total_others_income ?? 0) > 0)
                        <tr class="border-t border-dashed border-gray-300 dark:border-gray-600">
                            <td class="pt-3 pb-1 pr-4 font-semibold text-gray-500 dark:text-gray-400">Lainnya</td>
                            <td class="pt-3 pb-1 text-right font-medium text-gray-700 dark:text-gray-300">
                                Rp {{ number_format($property->total_others_income, 0, ',', '.') }}
                            </td>
                        </tr>
                    @endif
                </tbody>
                <tfoot class="text-gray-800 dark:text-gray-200">
                    <tr class="border-t-2 border-gray-300 dark:border-gray-700">
                        <td class="pt-3 pr-4 font-semibold">{{ $revenueTitle ?? 'Total Revenue' }}</td>
                        <td class="pt-3 text-right text-base font-bold">
                            Rp {{ number_format($property->dailyRevenue ?? 0, 0, ',', '.') }}
                        </td>
                    </tr>
                    <tr>
                        <td class="pt-1.5 pr-4 font-semibold">Average Room Rate</td>
                        <td class="pt-1.5 text-right text-base font-bold">
                            Rp {{ number_format($property->averageRoomRate ?? 0, 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <div class="mt-5">
        <a href="{{ route('admin.properties.show', $property->id) }}" 
           class="inline-block w-full text-center px-4 py-2 bg-blue-600 text-white rounded-lg shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200 text-sm font-semibold">
            Lihat Detail
        </a>
    </div>
</div>