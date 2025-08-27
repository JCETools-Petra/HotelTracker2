<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route; // Pastikan ini di-import jika belum

class RouteServiceProvider extends ServiceProvider
{
    /**
     * Jalur ke rute "home" aplikasi Anda.
     *
     * Biasanya, pengguna diarahkan ke sini setelah autentikasi.
     * Pastikan ini sesuai dengan nama rute '/dashboard' umum yang Anda buat di web.php.
     *
     * @var string
     */
    public const HOME = '/dashboard'; // << PASTIKAN NILAI INI ADALAH '/dashboard'

    /**
     * Tentukan model binding rute Anda, filter pola, dan konfigurasi rute lainnya.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->configureRateLimiting();

        // Catatan: Di Laravel 11, file rute (web.php, api.php, dll.)
        // biasanya dimuat melalui file bootstrap/app.php menggunakan ->withRouting().
        // Metode tradisional untuk mendefinisikan rute di sini (mapWebRoutes, mapApiRoutes)
        // sudah tidak umum digunakan secara default di Laravel 11.
        // Jika Anda memiliki logika pemuatan rute kustom, itu akan ada di sini atau
        // lebih mungkin di bootstrap/app.php.

        /*
        // Contoh jika Anda masih menggunakan metode lama atau memerlukan pemuatan kustom:
        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));

            // Jika Anda memiliki file rute lain, misalnya untuk channel:
            // Route::middleware('channels')
            //     ->group(base_path('routes/channels.php'));

            // Jika Anda memiliki file rute untuk console commands:
            // Commands::route()
            //     ->group(base_path('routes/console.php'));
        });
        */
    }

    /**
     * Konfigurasi rate limiter untuk aplikasi.
     *
     * @return void
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Contoh jika Anda memiliki rate limiter untuk login
        /*
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by($request->input('email').$request->ip());
        });
        */

        // Contoh jika Anda memiliki rate limiter untuk 'attempts' (verifikasi email, reset password)
        // Biasanya ini sudah diatur oleh Fortify atau Breeze jika Anda menggunakannya.
        /*
        RateLimiter::for('attempts', function (Request $request) {
            return Limit::perMinute(5)->by($request->input('email') . $request->ip());
        });
        */

        // Rate limiter untuk global (semua request) jika diperlukan
        /*
        RateLimiter::for('global', function (Request $request) {
            return Limit::perMinute(1000)->by($request->ip());
        });
        */
    }
}