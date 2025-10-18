<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use App\Mail\LowStockNotification;
use App\Models\Inventory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::all()->keyBy('key');
        $properties = \App\Models\Property::all(); // Tambahkan baris ini
        return view('admin.settings.index', compact('settings', 'properties')); // Tambahkan 'properties'
    }

    public function store(Request $request)
    {
        $this->authorize('manage-data');

        // 1. Validasi semua input, termasuk untuk notifikasi
        $validatedData = $request->validate([
            'app_name' => 'required|string|max:255',
            'logo_path' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'favicon_path' => 'nullable|image|mimes:png,ico|max:512',
            'logo_size' => 'nullable|integer|min:10',
            'sidebar_logo_size' => 'nullable|integer|min:10',
            // Validasi untuk pengaturan notifikasi stok rendah
            'low_stock_notification' => 'required|boolean',
            'low_stock_recipient_email' => 'nullable|email',
        ]);

        // 2. Simpan pengaturan yang berbasis teks/angka
        $textSettings = [
            'app_name', 'logo_size', 'sidebar_logo_size', 
            'low_stock_notification', 'low_stock_recipient_email'
        ];

        foreach ($textSettings as $key) {
            // Periksa apakah request memiliki key tersebut
            if ($request->has($key)) {
                Setting::updateOrCreate(['key' => $key], ['value' => $request->input($key)]);
            }
        }
        
        // 3. Menangani unggahan Logo Aplikasi (logika Anda sudah benar)
        if ($request->hasFile('logo_path')) {
            $oldLogo = Setting::where('key', 'logo_path')->first();
            if ($oldLogo && $oldLogo->value) {
                Storage::disk('public')->delete($oldLogo->value);
            }
            $path = $request->file('logo_path')->store('branding', 'public');
            Setting::updateOrCreate(['key' => 'logo_path'], ['value' => $path]);
        }

        // 4. Menangani unggahan Favicon (logika Anda sudah benar)
        if ($request->hasFile('favicon_path')) {
            $oldFavicon = Setting::where('key', 'favicon_path')->first();
            if ($oldFavicon && $oldFavicon->value) {
                Storage::disk('public')->delete($oldFavicon->value);
            }
            $faviconPath = $request->file('favicon_path')->store('branding', 'public');
            Setting::updateOrCreate(['key' => 'favicon_path'], ['value' => $faviconPath]);
        }

        // 5. Hapus cache dan kembalikan response
        Cache::forget('app_settings');

        return redirect()->route('admin.settings.index')->with('success', 'Pengaturan berhasil diperbarui.');
    }
    
    // app/Http/Controllers/Admin/SettingController.php

    public function sendTestMsqEmail(Request $request)
    {
        // [DEBUG] Memastikan fungsi terpanggil
        Log::info('--- [MSQ DEBUG] Proses kirim laporan manual dimulai (versi query baru). ---');
    
        $this->authorize('manage-data');
    
        $request->validate([
            'property_id' => 'required|exists:properties,id',
        ]);
        
        // [DEBUG] Melihat ID properti yang dipilih
        Log::info('[MSQ DEBUG] Property ID yang dipilih: ' . $request->property_id);
    
        try {
            $property = \App\Models\Property::findOrFail($request->property_id);
            Log::info('[MSQ DEBUG] Properti ditemukan: "' . $property->name . '"');
    
            $recipientEmail = Setting::where('key', 'low_stock_recipient_email')->value('value');
            Log::info('[MSQ DEBUG] Email penerima di pengaturan: ' . ($recipientEmail ?: 'Kosong'));
    
            if (empty($recipientEmail)) {
                Log::warning('[MSQ DEBUG] Proses dihentikan karena email penerima kosong.');
                return redirect()->route('admin.settings.index')
                                 ->with('error', 'Email penerima belum diatur di pengaturan.');
            }
    
            Log::info('[MSQ DEBUG] Menjalankan query untuk mencari barang (stok < MSQ ATAU MSQ tidak diatur)...');
            
            $lowStockItems = Inventory::where('property_id', $property->id)
                ->where(function ($query) {
                    $query->whereColumn('stock', '<', 'minimum_standard_quantity')
                          ->orWhereNull('minimum_standard_quantity')
                          ->orWhere('minimum_standard_quantity', 0);
                })
                ->get();
    
            // [DEBUG] Ini adalah log yang paling penting untuk diperiksa!
            Log::info('[MSQ DEBUG] Query selesai. Jumlah item yang memenuhi kriteria: ' . $lowStockItems->count());
    
            if ($lowStockItems->isEmpty()) {
                Log::info('[MSQ DEBUG] Tidak ada item ditemukan yang memenuhi kriteria. Email tidak jadi dikirim.');
                return redirect()->route('admin.settings.index')
                                 ->with('success', 'Tidak ada barang di bawah MSQ atau MSQ belum diatur untuk properti "' . $property->name . '". Email tidak dikirim.');
            }
    
            Log::info('[MSQ DEBUG] Mencoba mengirim email ke ' . $recipientEmail . ' dengan ' . $lowStockItems->count() . ' item.');
            
            Mail::to($recipientEmail)->send(new LowStockNotification($lowStockItems));
            
            Log::info('[MSQ DEBUG] Perintah Mail::send() berhasil dieksekusi.');
    
            return redirect()->route('admin.settings.index')
                             ->with('success', 'Laporan stok rendah untuk "' . $property->name . '" berhasil dikirim ke ' . $recipientEmail);
    
        } catch (\Exception $e) {
            Log::error('--- [MSQ DEBUG] TERJADI ERROR SAAT MENGIRIM EMAIL ---');
            Log::error('[MSQ DEBUG] Pesan: ' . $e->getMessage());
            Log::error('[MSQ DEBUG] File: ' . $e->getFile() . ' (Baris: ' . $e->getLine() . ')');
            
            return redirect()->route('admin.settings.index')
                             ->with('error', 'Gagal mengirim email. Silakan periksa file log untuk detailnya.');
        }
    }
}