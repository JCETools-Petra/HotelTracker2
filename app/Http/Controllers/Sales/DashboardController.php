<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $salesUser = auth()->user();
        $propertyId = $salesUser->property_id;

        // Jika sales tidak terikat ke properti, kembalikan data kosong
        if (!$propertyId) {
            return view('sales.dashboard', [
                'totalBookingThisMonth' => 0,
                'confirmedBookingThisMonth' => 0,
                'estimatedRevenueThisMonth' => 0,
                'totalParticipantsThisMonth' => 0,
                'upcomingEvents' => collect(),
                'latestBooking' => null
            ]);
        }
        
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        // Data untuk kartu statistik
        $totalBookingThisMonth = Booking::where('property_id', $propertyId)
            ->whereBetween('booking_date', [$startOfMonth, $endOfMonth])
            ->count();
            
        $confirmedBookingThisMonth = Booking::where('property_id', $propertyId)
            ->where('status', 'Booking Pasti')
            ->whereBetween('booking_date', [$startOfMonth, $endOfMonth])
            ->count();

        $estimatedRevenueThisMonth = Booking::where('property_id', $propertyId)
            ->where('status', 'Booking Pasti')
            ->whereBetween('event_date', [$startOfMonth, $endOfMonth])
            ->sum('total_price');

        $totalParticipantsThisMonth = Booking::where('property_id', $propertyId)
            ->where('status', 'Booking Pasti')
            ->whereBetween('event_date', [$startOfMonth, $endOfMonth])
            ->sum('participants');

        // PERBAIKAN: Eager load relasi 'miceCategory' untuk efisiensi
        $upcomingEvents = Booking::where('property_id', $propertyId)
            ->where('status', 'Booking Pasti')
            ->whereBetween('event_date', [Carbon::today(), Carbon::today()->addDays(7)])
            ->with('miceCategory') // <-- Eager loading relasi
            ->orderBy('event_date', 'asc')
            ->orderBy('start_time', 'asc')
            ->get();
            
        $latestBooking = Booking::where('property_id', $propertyId)
            ->latest()
            ->first();

        return view('sales.dashboard', compact(
            'totalBookingThisMonth',
            'confirmedBookingThisMonth',
            'estimatedRevenueThisMonth',
            'totalParticipantsThisMonth',
            'upcomingEvents',
            'latestBooking'
        ));
    }
}
