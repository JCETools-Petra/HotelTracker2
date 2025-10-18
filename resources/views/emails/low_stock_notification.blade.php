@component('mail::message')
{{-- Bagian Header dengan Logo --}}
@slot('header')
@component('mail::header', ['url' => config('app.url')])
{{ config('app.name') }}
@endcomponent
@endslot

{{-- Isi Email --}}
#  Laporan Stok Rendah & MSQ

**Kepada Yth. Bapak/Ibu,**

Berikut adalah laporan untuk barang-barang yang memerlukan perhatian. Daftar ini berisi barang yang stoknya di bawah jumlah minimum **atau** yang data MSQ-nya belum diatur.

@component('mail::table')
| Nama Barang | Stok Saat Ini | Stok Minimum | Keterangan |
|:--------------|:----------------|:---------------|:-------------|
@foreach ($lowStockItems as $item)
| {{ $item->name }} | {{ $item->stock }} | {{ $item->minimum_standard_quantity ?? 'N/A' }} | @if(is_null($item->minimum_standard_quantity) || $item->minimum_standard_quantity == 0) **MSQ Belum Diatur** @else Stok Rendah @endif |
@endforeach
@endcomponent

Untuk melihat detail lebih lanjut atau memperbaiki data, silakan klik tombol di bawah ini.

@component('mail::button', ['url' => route('admin.inventories.index'), 'color' => 'primary'])
Periksa Inventaris
@endcomponent

Hormat kami,<br>
Tim {{ config('app.name') }}

{{-- Bagian Footer (Subcopy) --}}
@slot('subcopy')
@component('mail::subcopy')
Ini adalah email yang dibuat secara otomatis oleh sistem. Mohon untuk tidak membalas email ini.
@endcomponent
@endslot
@endcomponent