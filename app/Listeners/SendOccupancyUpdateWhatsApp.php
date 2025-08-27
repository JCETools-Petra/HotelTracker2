<?php

namespace App\Listeners;

use App\Events\OccupancyUpdated;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SendOccupancyUpdateWhatsApp
{
    private static $hasRun = false;

    public function handle(OccupancyUpdated $event): void
    {
        if (self::$hasRun) {
            return;
        }
        self::$hasRun = true;

        $property = $event->property;
        $occupancy = $event->occupancy;

        $ecommerceUsers = User::where('role', 'online_ecommerce')
                           ->where('receives_whatsapp_notifications', true)
                           ->whereNotNull('phone_number')
                           ->get();

        if ($ecommerceUsers->isEmpty()) {
            return;
        }

        $fonnteToken = config('services.fonnte.token');
        if (!$fonnteToken) {
            Log::error('Token Fonnte tidak ditemukan di konfigurasi.');
            return;
        }

        // === AWAL PERUBAHAN ===
        $date = \Carbon\Carbon::parse($occupancy->date)->translatedFormat('l, d F Y');
        // Ubah waktu ke zona waktu 'Asia/Jayapura' (WIT / GMT+9)
        $time = now()->setTimezone('Asia/Jayapura')->format('H:i');

        $message = "🔔 *Update Okupansi*\n\n" .
                   "Properti: *{$property->name}*\n" .
                   "Tanggal: *{$date}*\n" .
                   // Ganti WIB menjadi WIT
                   "Waktu Update: *{$time} WIT*\n\n" .
                   "Total Terisi: *{$occupancy->occupied_rooms}*\n" .
                   "   - Reservasi OTA: {$occupancy->reservasi_ota}\n" .
                   "   - Input Properti: {$occupancy->reservasi_properti}\n\n" .
                   "Silakan cek dasbor untuk detail.\n" .
                   "https://hoteliermarket.my.id/";
        // === AKHIR PERUBAHAN ===

        $targets = $ecommerceUsers->pluck('phone_number')->implode(',');

        try {
            $response = Http::withHeaders([
                'Authorization' => $fonnteToken
            ])->post('https://api.fonnte.com/send', [
                'target' => $targets,
                'message' => $message,
                'countryCode' => '62',
            ]);

            if ($response->failed()) {
                Log::error('Gagal mengirim WhatsApp via Fonnte: ' . $response->body());
            }

        } catch (\Exception $e) {
            Log::error('Exception saat mengirim WhatsApp via Fonnte: ' . $e->getMessage());
        }
    }
}