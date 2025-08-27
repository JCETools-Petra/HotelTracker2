<?php

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

if (!function_exists('setting')) {
    /**
     * Mengambil nilai pengaturan dari database dengan dukungan cache.
     *
     * @param string $key Kunci pengaturan yang ingin diambil.
     * @param mixed $default Nilai default yang akan dikembalikan jika kunci tidak ditemukan.
     * @return mixed
     */
    function setting($key, $default = null)
    {
        // Mengambil semua pengaturan dari cache untuk meningkatkan performa.
        // Jika cache kosong, ambil dari database dan simpan di cache selamanya.
        try {
            $settings = Cache::rememberForever('app_settings', function () {
                // Pastikan tabel settings ada sebelum mencoba mengaksesnya.
                if (\Illuminate\Support\Facades\Schema::hasTable('settings')) {
                    return Setting::all()->keyBy('key');
                }
                return collect(); // Kembalikan koleksi kosong jika tabel tidak ada
            });

            // Kembalikan nilai dari pengaturan, atau nilai default jika tidak ada.
            return $settings->get($key)->value ?? $default;

        } catch (\Exception $e) {
            // Jika terjadi error (misalnya saat migrasi awal), kembalikan nilai default.
            return $default;
        }
    }
}
