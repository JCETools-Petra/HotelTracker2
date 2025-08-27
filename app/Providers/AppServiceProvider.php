<?php
// app/Providers/AppServiceProvider.php
namespace App\Providers;

use Illuminate\Support\Facades\View; // <-- Tambahkan ini
use Illuminate\Support\Facades\Cache; // <-- Tambahkan ini
use App\Models\Setting; // <-- Tambahkan ini
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void { /* ... */ }

    public function boot(): void
    {
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