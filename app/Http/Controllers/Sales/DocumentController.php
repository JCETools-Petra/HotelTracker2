<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
// PERBAIKAN: Gunakan 'use' statement yang benar untuk Facade PDF
use Barryvdh\DomPDF\Facade\Pdf;

class DocumentController extends Controller
{
    /**
     * Menghasilkan file PDF untuk Quotation.
     */
    public function generateQuotation(Booking $booking)
    {
        // Pastikan sales hanya bisa akses booking dari propertinya
        if (auth()->user()->property_id != $booking->property_id) {
            abort(403, 'Akses ditolak.');
        }

        $data = ['booking' => $booking];
        
        $pdf = Pdf::loadView('sales.documents.quotation', $data);

        return $pdf->download('quotation-'.$booking->booking_number.'.pdf');
    }

    /**
     * Menghasilkan file PDF untuk Invoice.
     */
    public function generateInvoice(Booking $booking)
    {
        if (auth()->user()->property_id != $booking->property_id) {
            abort(403, 'Akses ditolak.');
        }

        $data = ['booking' => $booking];
        $pdf = Pdf::loadView('sales.documents.invoice', $data);
        return $pdf->download('invoice-'.$booking->booking_number.'.pdf');
    }
    
    /**
     * Menghasilkan file PDF untuk BEO.
     */
    public function generateBeo(Booking $booking)
    {
        if (auth()->user()->property_id != $booking->property_id || !$booking->functionSheet) {
            return redirect()->back()->with('error', 'Aksi tidak diizinkan atau BEO belum dibuat.');
        }

        $data = [
            'booking' => $booking,
            'beo' => $booking->functionSheet
        ];

        $pdf = Pdf::loadView('sales.documents.beo-pdf', $data);
        return $pdf->stream('beo-'.$booking->booking_number.'.pdf');
    }
}
