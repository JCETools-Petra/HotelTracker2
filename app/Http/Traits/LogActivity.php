<?php

namespace App\Http\Traits;

use App\Models\ActivityLog; // Pastikan Anda punya model ini
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

trait LogActivity
{
    /**
     * Mencatat aktivitas pengguna ke dalam database.
     *
     * @param string $description Deskripsi dari aktivitas yang dilakukan.
     * @param Request $request Object request untuk mendapatkan IP & User Agent.
     */
    public function logActivity(string $description, Request $request)
    {
        if (!Auth::check()) {
            return; // Jangan catat jika tidak ada user yang login
        }

        $user = Auth::user();

        ActivityLog::create([
            'user_id'       => $user->id,
            'property_id'   => $user->property_id, // Catat properti terkait user
            'description'   => $description,
            'ip_address'    => $request->ip(),
            'user_agent'    => $request->userAgent(),
        ]);
    }
}