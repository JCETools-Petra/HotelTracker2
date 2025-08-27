<?php

// app/Http/Middleware/CheckRole.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response; // Pastikan ini Response dari Symfony

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles  // Menggunakan variadic parameters untuk menerima satu atau lebih peran
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // 1. Cek apakah pengguna sudah login
        //    Meskipun biasanya middleware 'auth' dijalankan sebelumnya, ini adalah lapisan keamanan tambahan.
        if (!Auth::check()) {
            // Jika belum, arahkan ke halaman login.
            return redirect('login');
        }

        $user = Auth::user(); // Ambil data pengguna yang sedang login

        // 2. Iterasi melalui setiap peran yang diizinkan (yang dilewatkan ke middleware)
        //    Contoh: ->middleware('role:admin'), maka $roles akan menjadi array ['admin']
        //    Contoh: ->middleware('role:pengguna_properti', 'editor'), maka $roles akan menjadi array ['pengguna_properti', 'editor']
        foreach ($roles as $role) {
            // 3. Periksa apakah peran pengguna cocok dengan salah satu peran yang diizinkan
            //    Perbandingan '==' di sini akan berfungsi baik jika $user->role dan $role keduanya string.
            //    Pastikan konsistensi case (besar/kecil huruf) jika sensitif.
            if ($user->role == $role) {
                // Jika peran cocok, izinkan request untuk melanjutkan ke rute berikutnya.
                return $next($request);
            }
        }

        // 4. Jika setelah memeriksa semua peran yang diizinkan tidak ada yang cocok,
        //    berarti pengguna tidak memiliki otorisasi.
        //    Hentikan request dengan error 403 (Forbidden).
        abort(403, 'Unauthorized action. Anda tidak memiliki peran yang sesuai untuk mengakses halaman ini.');
    }
}