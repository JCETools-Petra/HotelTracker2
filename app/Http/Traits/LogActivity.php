<?php

namespace App\Http\Traits;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

trait LogActivity
{
    /**
     * Mencatat aktivitas pengguna ke dalam database.
     *
     * @param string $description Deskripsi dari aktivitas yang dilakukan.
     * @param Request $request Object request untuk mendapatkan IP & User Agent.
     * @param int|null $propertyId ID dari properti yang terkait dengan aktivitas.
     */
    public function logActivity(string $description, Request $request, $propertyId = null)
    {
        if (!Auth::check()) {
            return; // Jangan catat jika tidak ada user yang login
        }

        ActivityLog::create([
            'user_id'       => Auth::id(),
            'property_id'   => $propertyId, // <-- DIUBAH: Mengambil dari parameter
            'description'   => $description,
            'ip_address'    => $request->ip(),
            'user_agent'    => $request->userAgent(),
        ]);
    }
}