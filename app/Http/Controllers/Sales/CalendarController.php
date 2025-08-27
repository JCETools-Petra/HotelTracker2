<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;

class CalendarController extends Controller
{
    /**
     * Menampilkan halaman utama kalender.
     */
    public function index()
    {
        return view('sales.calendar.index');
    }

    /**
     * Menyediakan data event untuk kalender dalam format JSON.
     */
    public function events(Request $request)
    {
        $salesUser = auth()->user();

        // Pastikan pengguna memiliki properti yang terikat
        if (!$salesUser->property_id) {
            return response()->json([]);
        }

        // Ambil semua booking dari properti milik sales yang sedang login
        // Eager load relasi 'room' untuk efisiensi dan menghindari N+1 query
        $bookings = Booking::with('room') 
            ->where('property_id', $salesUser->property_id)
            ->where('status', '!=', 'Cancel') // Abaikan booking yang sudah di-cancel
            ->get();

        $events = [];
        foreach ($bookings as $booking) {
            // Tentukan warna berdasarkan status booking
            $color = '#f59e0b'; // Default: Kuning untuk Booking Sementara
            if ($booking->status === 'Booking Pasti') {
                $color = '#10b981'; // Hijau untuk Booking Pasti
            }

            $events[] = [
                'title' => $booking->client_name . ' (' . ($booking->room->name ?? 'N/A') . ')',
                'start' => $booking->event_date->format('Y-m-d') . 'T' . $booking->start_time,
                'end' => $booking->event_date->format('Y-m-d') . 'T' . $booking->end_time,
                'url' => route('sales.bookings.edit', $booking), // Link saat event diklik
                'backgroundColor' => $color,
                'borderColor' => $color,
            ];
        }

        return response()->json($events);
    }
}
