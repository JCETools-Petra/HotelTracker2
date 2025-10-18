<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Inventory;
use Illuminate\Support\Facades\Mail;
use App\Mail\LowStockNotification;
use App\Models\Setting;

class CheckLowStock extends Command
{
    protected $signature = 'inventory:check-low-stock';
    protected $description = 'Check for items with low stock and send a notification';

    public function handle()
    {
        $lowStockItems = Inventory::whereColumn('quantity', '<', 'msq')->get();

        if ($lowStockItems->isNotEmpty()) {
            // Mengambil pengaturan dari database
            $notificationEnabled = Setting::where('key', 'low_stock_notification')->value('value');

            if ($notificationEnabled) {
                $recipientEmail = Setting::where('key', 'low_stock_recipient_email')->value('value');
                if ($recipientEmail) {
                    Mail::to($recipientEmail)->send(new LowStockNotification($lowStockItems));
                    $this->info('Email notifikasi stok rendah berhasil dikirim.');
                } else {
                    $this->info('Email penerima notifikasi stok rendah belum diatur.');
                }
            } else {
                $this->info('Notifikasi stok rendah dinonaktifkan.');
            }
        } else {
            $this->info('Tidak ada barang dengan stok rendah.');
        }

        return 0;
    }
}