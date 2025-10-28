<?php

namespace App\Listeners;

use App\Events\OccupancyUpdated;
use App\Models\User;
use App\Http\Traits\CalculatesBarPrices;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
// 1. Tambahkan 'Arr' untuk variasi kalimat
use Illuminate\Support\Arr;

class SendOccupancyUpdateWhatsApp
{
    use CalculatesBarPrices;

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

        // --- 2. Tentukan Zona Waktu dan Waktu Saat Ini ---
        $timezone = 'Asia/Jayapura';
        $now = now()->setTimezone($timezone);
        $date = \Carbon\Carbon::parse($occupancy->date)->translatedFormat('l, d F Y');
        $time = $now->format('H:i');
        $hour = (int) $now->format('H'); // Ambil jam untuk sapaan

        // --- 3. Logika Sapaan Dinamis ---
        $greeting = $this->getDynamicGreeting($hour);

        // --- 4. Logika Harga BAR (dari langkah sebelumnya) ---
        $occupiedRooms = $occupancy->occupied_rooms;
        $activeBarLevel = $this->getActiveBarLevel($occupiedRooms, $property);
        
        $barPricesMessage = "";
        $roomTypes = $property->roomTypes()->with('pricingRule')->get();

        foreach ($roomTypes as $roomType) {
            $price = $this->calculateActiveBarPrice($roomType, $activeBarLevel);
            $formattedPrice = 'Rp ' . number_format($price, 0, ',', '.');
            $barPricesMessage .= "   - {$roomType->name}: *{$formattedPrice}*\n";
        }

        // --- 5. Buat Variasi Kalimat ---
        $header = Arr::random([
            "🔔 *Update Okupansi*",
            "📊 *Laporan Okupansi Terbaru*",
            "🏨 *Info Okupansi Terkini*",
        ]);
        
        $intro = Arr::random([
            "berikut kami sampaikan update untuk:",
            "ini adalah laporan okupansi terbaru untuk:",
            "info terkini untuk properti:",
        ]);

        $signOff = Arr::random([
            "Silakan cek dasbor untuk detail.",
            "Detail lebih lengkap ada di dasbor.",
            "Cek dasbor Anda untuk rincian.",
        ]);

        $closing = Arr::random([
            "Semoga informasinya membantu!",
            "Terima kasih atas perhatiannya.",
            "Selamat melanjutkan aktivitas.",
            "Semangat selalu!",
        ]);


        // --- 6. Susun Pesan Utama ---
        $message = "{$header}\n\n" .
                   "{$greeting},\n" . // Sapaan dinamis
                   "{$intro}\n\n" . // Intro yang bervariasi
                   "Properti: *{$property->name}*\n" .
                   "Tanggal: *{$date}*\n" .
                   "Waktu Update: *{$time} WIT*\n\n" .
                   "Total Terisi: *{$occupancy->occupied_rooms}*\n" .
                   "   - Reservasi OTA: {$occupancy->reservasi_ota}\n" .
                   "   - Input Properti: {$occupancy->reservasi_properti}\n\n" .
                   "BAR Level Aktif: *BAR {$activeBarLevel}*\n" .
                   $barPricesMessage . "\n" .
                   "{$signOff}\n" . // Sign-off yang bervariasi
                   "https://hoteliermarket.my.id/\n\n" .
                   "{$closing}"; // Penutup yang ramah

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

    /**
     * Menentukan sapaan berdasarkan jam (Waktu Indonesia Timur / WIT).
     *
     * @param int $hour Jam dalam format 24-jam
     * @return string
     */
    private function getDynamicGreeting(int $hour): string
    {
        // 05:00 - 09:59 -> Pagi
        if ($hour >= 5 && $hour < 10) {
            return 'Selamat Pagi';
        }
        // 10:00 - 14:59 -> Siang
        if ($hour >= 10 && $hour < 15) {
            return 'Selamat Siang';
        }
        // 15:00 - 17:59 -> Sore
        if ($hour >= 15 && $hour < 18) {
            return 'Selamat Sore';
        }
        // 18:00 - 04:59 -> Malam
        return 'Selamat Malam';
    }
}