<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class SettingController extends Controller
{
    public function index()
    {
        // Mengambil semua pengaturan sebagai koleksi yang diindeks oleh 'key'
        $settings = Setting::all()->keyBy('key');
        return view('admin.settings.index', compact('settings'));
    }

    public function store(Request $request)
    {
        $this->authorize('manage-data');
        // Validasi input
        $validatedData = $request->validate([
            'app_name' => 'required|string|max:255',
            'logo_path' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'favicon_path' => 'nullable|image|mimes:png,ico|max:512', // Validasi untuk favicon
            'logo_size' => 'nullable|integer|min:10',
            'sidebar_logo_size' => 'nullable|integer|min:10',
        ]);

        // Proses dan simpan setiap pengaturan
        foreach ($validatedData as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }
        
        // Menangani unggahan Logo Aplikasi
        if ($request->hasFile('logo_path')) {
            // Hapus logo lama jika ada
            $oldLogo = Setting::where('key', 'logo_path')->first();
            if ($oldLogo && $oldLogo->value) {
                Storage::disk('public')->delete($oldLogo->value);
            }
            // Simpan logo baru
            $path = $request->file('logo_path')->store('branding', 'public');
            Setting::updateOrCreate(['key' => 'logo_path'], ['value' => $path]);
        }

        // ==============================================================
        // >> AWAL: Logika Baru untuk Menangani Unggahan Favicon <<
        // ==============================================================
        if ($request->hasFile('favicon_path')) {
            // Hapus favicon lama jika ada
            $oldFavicon = Setting::where('key', 'favicon_path')->first();
            if ($oldFavicon && $oldFavicon->value) {
                Storage::disk('public')->delete($oldFavicon->value);
            }
            // Simpan favicon baru
            $faviconPath = $request->file('favicon_path')->store('branding', 'public');
            Setting::updateOrCreate(['key' => 'favicon_path'], ['value' => $faviconPath]);
        }
        // ==============================================================
        // >> AKHIR: Logika Baru untuk Menangani Unggahan Favicon <<
        // ==============================================================

        // Hapus cache pengaturan agar perubahan langsung diterapkan
        Cache::forget('app_settings');

        return redirect()->route('admin.settings.index')->with('success', 'Pengaturan berhasil diperbarui.');
    }
}
