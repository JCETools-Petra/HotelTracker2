<?php

namespace App\Policies;

use App\Models\DailyIncome;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class DailyIncomePolicy
{
    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\DailyIncome  $dailyIncome
     * @return bool
     */
    public function update(User $user, DailyIncome $dailyIncome): bool
    {
        // Izinkan update jika role pengguna adalah 'pengguna_properti'
        // DAN ID properti pengguna sama dengan ID properti pada data pendapatan.
        return $user->role === 'pengguna_properti' && $user->property_id === $dailyIncome->property_id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\DailyIncome  $dailyIncome
     * @return bool
     */
    public function delete(User $user, DailyIncome $dailyIncome): bool
    {
        // Izinkan hapus jika role pengguna adalah 'pengguna_properti'
        // DAN ID properti pengguna sama dengan ID properti pada data pendapatan.
        return $user->role === 'pengguna_properti' && $user->property_id === $dailyIncome->property_id;
    }
}
