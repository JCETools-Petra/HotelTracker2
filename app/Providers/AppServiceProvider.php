<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Blade; // <-- Tambahan baru
use App\Models\Setting;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // ...
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Mendaftarkan alias untuk komponen layout inventaris
        Blade::component('layouts.inventory', 'inventory-layout'); // <-- Tambahan baru

        // Coba ambil pengaturan dari cache
        try {
            $settings = Cache::remember('app_settings', 60, function () {
                // Pastikan tabel settings ada sebelum menjalankan query
                if (\Illuminate\Support\Facades\Schema::hasTable('settings')) {
                    return Setting::pluck('value', 'key');
                }
                return collect(); // Kembalikan koleksi kosong jika tabel tidak ada
            });

            // Bagikan data pengaturan ke semua view
            View::share('appSettings', $settings);

        } catch (\Exception $e) {
            // Tangani error jika terjadi (misalnya, saat migrasi awal)
            // Dengan cara ini, aplikasi tidak akan crash saat `php artisan migrate`
            View::share('appSettings', collect());
        }
    }
}