<?php

namespace App\Providers;

use App\Models\User; // <-- TAMBAHKAN INI
use Illuminate\Support\Facades\Gate; // <-- TAMBAHKAN INI
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // ======================= AWAL BLOK YANG DIUBAH =======================
        
        // Gate ini akan digunakan untuk semua aksi Create, Update, Delete (CUD)
        // Hanya user dengan peran 'admin' yang akan diizinkan.
        Gate::define('manage-data', function (User $user) {
            return $user->role === 'admin';
        });

        // Anda bisa juga membuat Gate yang lebih spesifik jika diperlukan
        // Gate::define('manage-users', fn(User $user) => $user->role === 'admin');
        // Gate::define('manage-properties', fn(User $user) => $user->role === 'admin');

        // ======================= AKHIR BLOK YANG DIUBAH ======================
    }
}