<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        'App\Models\DailyIncome' => 'App\Policies\DailyIncomePolicy', // Pastikan policy ini ada jika digunakan
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        /**
         * Gate ini hanya akan memberikan izin 'true' jika peran pengguna
         * adalah 'admin' atau 'owner'. Peran 'pengurus' dan lainnya
         * akan mendapatkan 'false', sehingga hanya bisa melihat.
         */
        Gate::define('manage-data', function (User $user) {
            return in_array($user->role, ['admin', 'owner']);
        });
    }
}