<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
        ]);

        // TAMBAHKAN 'property' DI DALAM with()
        $logs = ActivityLog::with(['user', 'property'])
            ->latest()
            // ... (sisa query Anda sudah bagus)
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->input('search');
                $query->where(function ($q) use ($search) {
                    $q->where('description', 'like', "%{$search}%")
                      ->orWhereHas('user', function ($userQuery) use ($search) {
                          $userQuery->where('name', 'like', "%{$search}%");
                      })
                      // Tambahkan pencarian berdasarkan nama properti
                      ->orWhereHas('property', function ($propertyQuery) use ($search) {
                        $propertyQuery->where('name', 'like', "%{$search}%");
                      });
                });
            })
            ->when($request->filled('start_date'), function ($query) use ($request) {
                $query->whereDate('created_at', '>=', $request->input('start_date'));
            })
            ->when($request->filled('end_date'), function ($query) use ($request) {
                $query->whereDate('created_at', '<=', $request->input('end_date'));
            })
            ->paginate(25)
            ->withQueryString();

        return view('admin.activity_log.index', compact('logs'));
    }
}